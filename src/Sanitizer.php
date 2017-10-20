<?php

namespace Syslogic\Sanny;

use Syslogic\Sanny\Exception\InvalidArgumentException;

/**
 * Sanitize Html filter.
 */
class Sanitizer
{
	/** @var string */
	protected $tidyConfigPath;

	/** @var SanitizationConfig */
	private $config;

	public function __construct(string $tidyConfigPath, SanitizationConfig $config)
	{
		if (!($tidyConfigPath = realpath($tidyConfigPath))) {
			throw new InvalidArgumentException('Invalid tidy configuration given');
		}

		$this->tidyConfigPath = $tidyConfigPath;
		$this->config = $config;
	}

	/**
	 * @param $contents
	 * @return string
	 */
	public function sanitize(string $contents)
	{
		$entitiesMap = $this->config->getEntitiesMap();
		$contents = str_replace(array_keys($entitiesMap), array_values($entitiesMap), $contents);

		//Strip paragraphs in headings
		$contents = preg_replace('%(<h[1-6]>\\s*?)<p>([^<]*?)</p>(\\s*?</h[1-6]>)%', '\\1\\2\\3', $contents);

		//Replace word-tags
		$contents = str_replace(["<o:p>", "</o:p>"], ["<span>", "</span>"], $contents);

		$DOMDocument = new \DomDocument('1.0', 'UTF-8');
		@$DOMDocument->loadHTML('<?xml encoding="UTF-8"><section>'.$contents . '</section>');

		$container = $DOMDocument->getElementsByTagName("body")->item(0)->childNodes->item(0);

		$elements = $this->collectElements($container);
		$this->parseElements($elements, $DOMDocument);

		$contents = $DOMDocument->saveHTML();

		$contents = str_replace(array_values($entitiesMap), array_keys($entitiesMap), $contents);

		$tidy = new \tidy();
		return $tidy->repairString($contents, $this->tidyConfigPath, 'utf8');
	}

	private function collectElements(\DOMElement $DOMElement, &$elements = [])
	{
		foreach($DOMElement->childNodes as $childNode)
		{
			if($childNode instanceof \DOMElement)
			{
				$elements[] = $childNode;
				$this->collectElements($childNode, $elements);
			}
		}

		return $elements;
	}

	/**
	 * @param \DOMElement[] $elements
	 */
	private function parseElements(array $elements, \DOMDocument $DOMDocument)
	{
		foreach($elements as $element) {
			$nodeSettings = $this->config->getNodeSettings($element->tagName);

			switch($nodeSettings['mode'])
			{
				case SanitizationConfig::ELEMENT_ALLOW:
					$this->parseAttributes($element);
					break;
				case SanitizationConfig::ELEMENT_STRIP:
					self::replaceNode(
						$element,
						$DOMDocument->createDocumentFragment()
					);
					continue;
				case SanitizationConfig::ELEMENT_REMOVE:
					if($element->parentNode) {
						$element->parentNode->removeChild($element);
					}
					continue;
				case SanitizationConfig::ELEMENT_CUSTOM:
					$result = $nodeSettings['handler']($element, function(\DOMAttr $attribute, \DOMElement $element) {
						$this->parseAttribute($attribute, $element);
					});

					//Could already be removed by the handler itself.
					if($result === false && $element->parentNode) {
						$element->parentNode->removeChild($element);
					}
					break;
			}
		}
	}

	private function parseAttributes(\DOMElement $element)
	{
		$attributes = $element->attributes;

		/** @var \DOMAttr $attribute */
		for ($i = $attributes->length; --$i >= 0; ) {
			$attribute = $attributes->item($i);
			$this->parseAttribute($attribute, $element);
		}
	}

	private function parseAttribute(\DOMAttr $attribute, \DOMElement $element)
	{
		$evaluator = $this->config->getAttributeEvaluator($attribute->name, $element->tagName);

		$value = trim($attribute->value);
		$result = $evaluator($value);

		if ($result === false) {
//				var_dump("deze keur ik af", $element->tagName, $attribute->name, $value);
//				echo "\n\n";
			$element->removeAttribute($attribute->name);
		} else if($result !== $value) {
//				var_dump($value,  $result);
//				echo "\n\n";
			$attribute->value = $result;
		}
	}

	private static function replaceNode(\DOMNode $oldNode, \DOMNode $newNode)
	{
		$childNodes = [];
		foreach ($oldNode->childNodes as $childNode) {
			$childNodes[] = $childNode;
		}

		foreach($childNodes as $innerNode) {
			$newNode->appendChild($innerNode);
		}

		$oldNode->parentNode->replaceChild($newNode, $oldNode);
	}

}
