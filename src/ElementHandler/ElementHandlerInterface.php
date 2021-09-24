<?php

namespace Scienta\Sanny\ElementHandler;

interface ElementHandlerInterface
{
	/**
	 * Handle the DOM sanitization of an element
	 *
	 * @param \DOMElement $element The alement to do the custom handling for
	 * @param callable $attributeParser Handle to the attribute parser. The signature is:
	 *      $attributeParser(\DOMAttr $attribute, \DOMElement $element);
	 *
	 * @return false|void
	 */
	public function __invoke(\DOMElement $element, callable $attributeParser);
}
