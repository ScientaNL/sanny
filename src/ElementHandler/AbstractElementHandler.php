<?php

namespace Scienta\Sanny\ElementHandler;

abstract class AbstractElementHandler implements ElementHandlerInterface
{
	protected function parseAttributes(callable $attributeParser, \DOMElement $element, array $ignoreAttributes = [])
	{
		$attributes = [];
		for ($i = $element->attributes->length; --$i >= 0;) {
			/** @var \DOMAttr $attribute */
			$attribute = $element->attributes->item($i);
			$attributes[$attribute->name] = $attribute;
		}

		foreach ($attributes as $attribute) {
			if (in_array($attribute->name, $ignoreAttributes) === false) {
				$attributeParser($attribute, $element);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function __invoke(\DOMElement $element, callable $attributeParser)
	{
	}
}
