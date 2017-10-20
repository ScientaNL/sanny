<?php

namespace Syslogic\Sanny\ElementHandler;

use Syslogic\Sanny\AttributeEvaluator\UriEvaluator;

class IframeEmbedHandler extends AbstractElementHandler implements ElementHandlerInterface
{
	/** @var UriEvaluator */
	private $uriEvaluator;

	private $allowedRegex;

	public function __construct(UriEvaluator $uriEvaluator, string $allowedRegex)
	{
		$this->uriEvaluator = $uriEvaluator;
		$this->allowedRegex = $allowedRegex;
	}

	public function __invoke(\DOMElement $element, callable $attributeParser)
	{
		if (!$element->attributes->getNamedItem("src")) {
			return false;
		}

		$srcAttr = $element->attributes->getNamedItem("src");
		$src = $srcAttr->nodeValue;
		$uriEvaluator = $this->uriEvaluator;

		if(($src = $uriEvaluator($src)) === false) {
			return false;
		}

		if(!preg_match($this->allowedRegex, $src)) {
			return false;
		}

		$srcAttr->nodeValue = $src;

		// Parse all other attributes according to the settings
		$this->parseAttributes($attributeParser, $element, ["src"]);
	}
}
