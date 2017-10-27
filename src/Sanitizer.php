<?php

namespace Syslogic\Sanny;

use Syslogic\Sanny\Exception\UnwantedElementException;

class Sanitizer
{
	/** @var SanitizationConfig */
	private $config;

	public function __construct(SanitizationConfig $config)
	{
		$this->config = $config;
	}

	public function sanitize(string $contents): string
	{
		$contents = $this->transformEntitiesToPlaceholders($contents);

		//Strip paragraphs in headings
		$contents = preg_replace('%(<h[1-6]>\\s*?)<p>([^<]*?)</p>(\\s*?</h[1-6]>)%', '\\1\\2\\3', $contents);

		//Replace word-tags
		$contents = str_replace(["<o:p>", "</o:p>"], ["<span>", "</span>"], $contents);

		$document = new \DomDocument('1.0', 'UTF-8');
		$document->substituteEntities = false;
		@$document->loadHTML(
			'<?xml encoding="UTF-8"><html><body><section>'.$contents.'</section></body></html>'
		);

		$container = $document->getElementsByTagName("body")->item(0)->childNodes->item(0);
		$document->normalizeDocument();

		$this->walkDOM($container, $document);

		$contents = $document->saveHTML();

		$contents = substr(
			$contents,
			($start = strpos($contents, "<section>") + 9),
			strrpos($contents, "</section>") - $start
		);

		$contents = $this->transformPlaceholdersToEntities($contents);

		$formatter = new Formatter(
			[
				"indent" => $this->config->getIndent() ? "auto" : false,
				"quiet" => true,
				"output-xhtml" => true,
				"preserve-entities" => true,
				"vertical-space" => false,
				"show-body-only" => true,
				"wrap" => false,
			]
		);

		return $formatter->format($contents);
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
			}
		}

		if ($depth > 0) {
			$this->parseElement($element, $document);
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
			@$attribute->value = $result;
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
}
