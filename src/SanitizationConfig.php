<?php

namespace Syslogic\Sanny;

use Syslogic\Sanny\Exception\InvalidArgumentException;

final class SanitizationConfig
{
	const ELEMENT_ALLOW = "allow";
	const ELEMENT_STRIP = "strip";
	const ELEMENT_REMOVE = "remove";
	const ELEMENT_CUSTOM = "custom";

	const DEFAULT_KEY = "*";

	private $entitiesMap = [
		"&nbsp;" => "{{@nbsp;}}",
		"&quot;" => "{{@quot;}}",
	];

	private $elementSettings = [];
	private $attributeSettings = [];
	private $elementCallbacks = [];

	/** @var callable */
	private $emptyElementHandler;

	private $indent = false;

	public function __construct(string $defaultElementMode = self::ELEMENT_STRIP, bool $whitelistAttributes = true)
	{
		//Default behaviour
		$this->addElement(self::DEFAULT_KEY, $defaultElementMode);

		if ($whitelistAttributes === true) {
			$this->addAttribute(
				self::DEFAULT_KEY,
				function () {
					return false;
				}
			);
		}
	}

	public function addElement(string $tagName, string $mode = self::ELEMENT_ALLOW, callable $customHandler = null)
	{
		$this->elementSettings[$tagName] = ["mode" => $mode, "handler" => $customHandler];
	}

	public function addElementCallback(string $tagName, callable $callback)
	{
		if (isset($this->elementSettings[$tagName]) === false) {
			throw new InvalidArgumentException(
				sprintf("Cannot add callback to element '%s' which is not added to the configuration", $tagName)
			);
		} elseif ($this->elementSettings[$tagName]['mode'] !== self::ELEMENT_ALLOW) {
			throw new InvalidArgumentException(
				sprintf("Cannot add callback to element '%s' which is not set to mode 'allow'", $tagName)
			);
		}

		if (isset($this->elementCallbacks[$tagName]) === false) {
			$this->elementCallbacks[$tagName] = [];
		}

		$this->elementCallbacks[$tagName][] = $callback;
	}

	public function getElementCallbacks(string $tagName): array
	{
		return (isset($this->elementCallbacks[$tagName]) === true) ? $this->elementCallbacks[$tagName] : [];
	}

	public function addAttribute(string $attributeName, callable $evaluator, array $onlyOnElements = [])
	{
		$this->attributeSettings[$attributeName] = [$evaluator, $onlyOnElements];
	}

	public function getElementSettings(string $tagName): array
	{
		return isset($this->elementSettings[$tagName]) === true
			? $this->elementSettings[$tagName]
			: $this->elementSettings[self::DEFAULT_KEY];
	}

	public function getAttributeEvaluator(string $attributeName, string $tagName): callable
	{
		if (isset($this->attributeSettings[$attributeName]) === true) {
			list($evaluator, $onlyOnElements) = $this->attributeSettings[$attributeName];

			if (empty($onlyOnElements) === true || in_array($tagName, $onlyOnElements) === true) {
				return $evaluator;
			}
		}

		list($evaluator) = $this->attributeSettings[self::DEFAULT_KEY];

		return $evaluator;
	}

	public function getEntitiesMap()
	{
		return $this->entitiesMap;
	}

	public function getEmptyElementHandler()
	{
		return $this->emptyElementHandler;
	}

	public function setEmptyElementHandler(callable $emptyElementHandler)
	{
		$this->emptyElementHandler = $emptyElementHandler;
	}

	public function getIndent(): bool
	{
		return $this->indent;
	}

	public function setIndent(bool $indent)
	{
		$this->indent = $indent;
	}
}
