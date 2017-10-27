<?php

namespace Syslogic\Sanny;

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

	private $nodeSettings = [];
	private $attributeSettings = [];

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
		$this->nodeSettings[$tagName] = ["mode" => $mode, "handler" => $customHandler];
	}

	public function addAttribute(string $attributeName, callable $evaluator, array $onlyOnElements = [])
	{
		$this->attributeSettings[$attributeName] = [$evaluator, $onlyOnElements];
	}

	public function getNodeSettings(string $tagName): array
	{
		return isset($this->nodeSettings[$tagName]) === true
			? $this->nodeSettings[$tagName]
			: $this->nodeSettings[self::DEFAULT_KEY];
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
