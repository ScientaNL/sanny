<?php

namespace Syslogic\Sanny;

use Syslogic\Sanny\Exception\AttributeInvalidValueException;
use Syslogic\Sanny\Exception\InvalidArgumentException;
use Syslogic\Sanny\Exception\UnwantedElementException;
use Syslogic\Sanny\PostprocessDOMHandler\PostProcessDOMHandlerInterface;
use Syslogic\Sanny\PreProcessHtmlHandler\PreProcessHtmlHandlerInterface;

class Sanitizer
{
	public static $dinges = [];

	/** @var SanitizationConfig */
	private $config;

	private $indentationOffset = -1;

	public function __construct(SanitizationConfig $config)
	{
		$this->config = $config;
	}

	public function sanitize(string $contents): string
	{
		$contents = $this->preProcessHtml($contents);

		$contents = $this->transformEntitiesToPlaceholders($contents);

		$document = new \DomDocument('1.0', 'UTF-8');
		$document->substituteEntities = false;
		$document->preserveWhiteSpace = true;
		$document->formatOutput = true;

		@$document->loadHTML(
			'<?xml encoding="UTF-8"><html><body>'.$contents.'</body></html>'
		);

		$document->normalizeDocument();

		$this->walkDOM($rootElement = $document->getElementsByTagName("body")->item(0), $document);
		$this->postProcessHtml($rootElement, $document);

		$contents = $document->saveHTML();

		$contents = substr(
			$contents,
			($start = strpos($contents, "<body>") + 6),
			strrpos($contents, "</body>") - $start
		);

		$contents = $this->transformPlaceholdersToEntities($contents);

		return trim($contents, $this->config->getNewlineCharacter());
	}

	private function walkDOM(\DOMElement $element, \DOMDocument $document, int $depth = 0)
	{
		$childNodes = $element->childNodes;

		for ($i = $childNodes->length; --$i >= 0;) {
			$childNode = $childNodes->item($i);

			if ($childNode instanceof \DOMElement) {
				$this->walkDOM($childNode, $document, $depth + 1);
			} elseif ($childNode instanceof \DOMComment) {
				$this->removeNode($childNode);
			} elseif ($childNode instanceof \DOMText) {
				$this->stripUnwantedCharacters($childNode);
				$this->stripIndentation($childNode);
			}
		}

		if ($depth > 0) {
			$this->parseElement($element, $document);
			$this->indentDOMNode($element, $document, $depth);
		}

		if ($childNodes->length <= 0 && is_callable($handler = $this->config->getEmptyElementHandler())) {
			$handler($element);
		}
	}

	private function parseElement(\DOMElement $element, \DOMDocument $document)
	{
		$tagName = $element->tagName;
		$elementSettings = $this->config->getElementSettings($tagName);

		try {
			switch ($elementSettings['mode']) {
				case SanitizationConfig::ELEMENT_ALLOW:
					$this->parseAttributes($element);

					foreach ($this->config->getElementCallbacks($tagName) as $callback) {
						$callback($element);
					}
					break;
				case SanitizationConfig::ELEMENT_STRIP:
					$this->replaceNode(
						$element,
						$document->createDocumentFragment()
					);
					continue;
				case SanitizationConfig::ELEMENT_REMOVE:
					$this->removeNode($element);
					continue;
				case SanitizationConfig::ELEMENT_CUSTOM:
					$result = $elementSettings['handler'](
						$element,
						function (\DOMAttr $attribute, \DOMElement $element) {
							$this->parseAttribute($attribute, $element);
						}
					);

					if ($result === false) {
						$this->removeNode($element);
					}
					break;
			}
		} catch (UnwantedElementException $exception) {
			$this->removeNode($element);
		}
	}

	private function stripIndentation(\DOMText $textNode)
	{
		//Check if the current node has a preformatted as parent
		$cursor = $textNode;
		$preformattedElements = $this->config->getPreformattedElements();
		while ($cursor = $cursor->parentNode) {
			if (in_array($cursor->nodeName, $preformattedElements) === true) {
				return;
			}
		}

		$value = $textNode->nodeValue;

		//Bring it down to a single space
		$value = str_replace(["\r", "\n", "\t"], ' ', $value);
		while (strpos($value, '  ') !== false) {
			$value = str_replace('  ', ' ', $value);
		}

		if (in_array($textNode->parentNode->nodeName, $this->config->getInlineElements()) === false) {

			// Handle leading whitespace in nodeValue
			if (($previousSibling = $textNode->previousSibling) === null
				|| in_array($previousSibling->nodeName, $this->config->getInlineElements()) === false
			) {
				/**
				 * Due to all manipulations, two DOMTexts could be adjectent to each other, don't trim in that case.
				 * It could also be that the previous sibbling is a DOMComment which will be removed, so don't trim.
				 */
				if ($previousSibling instanceof \DOMText === false
					&& $previousSibling instanceof \DOMComment === false
				) {
					$value = ltrim($originalValue = $value);
				}
			}

			// Handle trailing whitespace in nodeValue
			if (($nextSibling = $textNode->nextSibling) === null
				|| in_array($nextSibling->nodeName, $this->config->getInlineElements()) === false
			) {
				// See above
				if ($nextSibling instanceof \DOMText === false
					&& $nextSibling instanceof \DOMComment === false
				) {
					$value = rtrim($value);
				}
			}
		}

		if (strlen($value) <= 0) {
			$this->removeNode($textNode);
		} else {
			$textNode->nodeValue = $value;
		}
	}

	private function parseAttributes(\DOMElement $element)
	{
		$attributes = $element->attributes;

		/** @var \DOMAttr $attribute */
		for ($i = $attributes->length; --$i >= 0;) {
			$attribute = $attributes->item($i);
			$this->parseAttribute($attribute, $element);
		}
	}

	private function parseAttribute(\DOMAttr $attribute, \DOMElement $element)
	{
		$value = $this->transformPlaceholdersToEntities(trim($attribute->value));

		$evaluator = $this->config->getAttributeEvaluator($attribute->name, $element->tagName);
		$result = $evaluator($value);

		if ($result === false) {
			$element->removeAttribute($attribute->name);
		} elseif ($result !== $value) {

			//Malformed values will result in a PHP warning and an removed value. Throw an error to get more control
			set_error_handler(
				function (int $errno, string $errstr) {
					throw new \Error($errstr, $errno);
				}
			);

			try {
				@$attribute->value = $result;
			} catch (\Error $e) {
				throw new AttributeInvalidValueException(
					sprintf(
						"Could not set value '%s' to attribute '%s' of element '%s' (%s)",
						$value,
						$attribute->name,
						$element->tagName,
						$e->getMessage()
					)
				);
			} finally {
				restore_error_handler();
			}
		}
	}

	private function replaceNode(\DOMNode $oldNode, \DOMNode $newNode)
	{
		$childNodes = $oldNode->childNodes;

		while ($childNodes->length) {
			$newNode->appendChild($childNodes->item(0));
		}

		$oldNode->parentNode->replaceChild($newNode, $oldNode);
	}

	private function removeNode(\DOMNode $node)
	{
		if ($node->parentNode) {
			$node->parentNode->removeChild($node);
		}
	}

	private function transformEntitiesToPlaceholders(string $contents): string
	{
		$entitiesMap = $this->config->getEntitiesMap();

		return str_replace(array_keys($entitiesMap), array_values($entitiesMap), $contents);
	}

	private function transformPlaceholdersToEntities(string $contents): string
	{
		$entitiesMap = $this->config->getEntitiesMap();

		return str_replace(array_values($entitiesMap), array_keys($entitiesMap), $contents);
	}

	/**
	 * Based on the work of https://github.com/wasinger/html-pretty-min
	 */
	private function indentDOMNode(\DOMNode $node, \DOMDocument $document, int $depth = 0)
	{
		if ($node instanceof \DOMText === true) {
			$indentNode = false;
			$indentChildren = true;
		} elseif (in_array($node->nodeName, $this->config->getInlineElements())) {
			$indentNode = $depth <= 1;
			$indentChildren = true;
		} elseif (in_array($node->nodeName, $this->config->getPreformattedElements()) === true) {
			$indentNode = true;
			$indentChildren = false;
		} else {
			$indentNode = true;
			$indentChildren = true;
		}

		/**
		 * Indenting a node consists of inserting before it a new text node containing a newline followed
		 * by a number of tabs corresponding to the node depth.
		 */
		if ($indentNode === true && $depth > 0 && $node->parentNode) {
			$node->parentNode->insertBefore(
				$document->createTextNode($this->getIndentationString($depth)),
				$node
			);
		}

		/**
		 * If children have been indented, then the closing tag of the current node must also be indented.
		 * This is done by adding a textNode as last child of the current node.
		 */
		if ($indentChildren === true) {
			$lastChild = $node->childNodes->item($node->childNodes->length - 1);
			if ($lastChild instanceof \DOMElement === true
				&& in_array($lastChild->nodeName, $this->config->getInlineElements()) === false
			) {
				$node->appendChild($document->createTextNode($this->getIndentationString($depth)));
			}
		}
	}

	private function getIndentationString(int $depth): string
	{
		$depth += $this->indentationOffset;

		return $this->config->getNewlineCharacter()
			.($depth > 0 ? str_repeat($this->config->getIndentCharacter(), $depth) : "");
	}

	private function stripUnwantedCharacters(\DOMText $node)
	{
		$node->nodeValue = str_replace(
			["\u{200a}", "\u{200b}", "\u{200c}", "\u{200d}", "\u{feff}"],
			'',
			$node->nodeValue
		);
	}

	private function preProcessHtml(string $html): string
	{
		/**
		 * Typehint to help IDE. Strictly speaking it is a callable which must respect this interface
		 * @var PreProcessHtmlHandlerInterface $handler
		 */
		foreach ($this->config->getPreProcessHtmlHandlers() as $handler) {
			$html = $handler($html);
		}

		return $html;
	}

	private function postProcessHtml(\DOMElement $rootElement, \DOMDocument $document)
	{
		/**
		 * Typehint to help IDE. Strictly speaking it is a callable which must respect this interface
		 * @var PostProcessDOMHandlerInterface $handler
		 */
		foreach ($this->config->getPostProcessDOMHandlers() as $handler) {
			$handler($rootElement, $document);
		}
	}
}
