<?php

namespace Scienta\Sanny;

use Scienta\Sanny\Exception\InvalidArgumentException;

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

	private $preformattedElements = ['script', 'style', 'pre', 'code'];
	private $inlineElements = [
		'b',
		'big',
		'i',
		'small',
		'tt',
		'u',
		'abbr',
		'acronym',
		'cite',
		'code',
		'dfn',
		'em',
		'kbd',
		'strong',
		'samp',
		'var',
		'a',
		'bdo',
		'br',
		'img',
		'q',
		'span',
		'sub',
		'sup',
		'button',
		'input',
		'label',
		'select',
		'textarea',
		'font',
		'center',
		'del',
		'dir',
		'ins',
		'rp',
		'rt',
		's',
		'source',
		'strike',
		'summary',
		'time',
		'wbr',
	];

	private $newlineCharacter = "\n";
	private $indentCharacter = "\t";

	private $preProcessHtmlHandlers = [];
	private $postProcessDOMHandlers = [];

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
		if (isset($this->elementSettings[$tagName]) === true) {
			throw new InvalidArgumentException(sprintf("Tag '%s' is already been set.", $tagName));
		}

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
		if (isset($this->attributeSettings[$attributeName]) === true) {
			throw new InvalidArgumentException(sprintf("Attribute '%s' is already been set.", $attributeName));
		}

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

	public function getPreformattedElements(): array
	{
		return $this->preformattedElements;
	}

	public function setPreformattedElements(array $preformattedElements)
	{
		$this->preformattedElements = $preformattedElements;
	}

	public function getInlineElements(): array
	{
		return $this->inlineElements;
	}

	public function setInlineElements(array $inlineElements)
	{
		$this->inlineElements = $inlineElements;
	}

	public function getNewlineCharacter(): string
	{
		return $this->newlineCharacter;
	}

	public function setNewlineCharacter(string $newlineCharacter)
	{
		$this->newlineCharacter = $newlineCharacter;
	}

	public function getIndentCharacter(): string
	{
		return $this->indentCharacter;
	}

	public function setIndentCharacter(string $indentCharacter)
	{
		$this->indentCharacter = $indentCharacter;
	}

	/**
	 * @return callable[]
	 */
	public function getPreProcessHtmlHandlers(): array
	{
		return $this->preProcessHtmlHandlers;
	}

	public function addPreProcessHtmlHandler(callable $handler)
	{
		$this->preProcessHtmlHandlers[] = $handler;
	}

	/**
	 * @return callable[]
	 */
	public function getPostProcessDOMHandlers(): array
	{
		return $this->postProcessDOMHandlers;
	}

	public function addPostProcessDOMHandlers(callable $handler)
	{
		$this->postProcessDOMHandlers[] = $handler;
	}
}
