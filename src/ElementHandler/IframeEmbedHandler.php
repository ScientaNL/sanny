<?php

namespace Syslogic\Sanny\ElementHandler;

use Syslogic\Sanny\AttributeEvaluator\UriEvaluator;

class IframeEmbedHandler extends AbstractElementHandler implements ElementHandlerInterface
{
	/** @var UriEvaluator */
	private $uriEvaluator;

	private $allowedHostsMap;

	/**
	 * @param UriEvaluator $uriEvaluator
	 * @param array $allowedHostsMap Key-value map with host as key and regex or `true` as value.
	 */
	public function __construct(UriEvaluator $uriEvaluator, array $allowedHostsMap)
	{
		$this->uriEvaluator = $uriEvaluator;
		$this->allowedHostsMap = $allowedHostsMap;
	}

	public function __invoke(\DOMElement $element, callable $attributeParser)
	{
		if (!$element->attributes->getNamedItem("src")) {
			return false;
		}

		$srcAttr = $element->attributes->getNamedItem("src");
		$src = $srcAttr->nodeValue;
		$uriEvaluator = $this->uriEvaluator;

		if (($src = $uriEvaluator($src)) === false) {
			return false;
		}

		$uriParser = $uriEvaluator->getUriParser();
		$components = $uriParser($src);

		if (isset($this->allowedHostsMap[$components['host']]) === false) {
			return false;
		} elseif (is_string($regex = $this->allowedHostsMap[$components['host']]) === true
			&& !preg_match($regex, $src)
		) {
			return false;
		}

		$srcAttr->nodeValue = $src;

		// Parse all other attributes according to the settings
		$this->parseAttributes($attributeParser, $element, ["src"]);
	}
}
