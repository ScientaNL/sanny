<?php

namespace Scienta\Sanny\PostprocessDOMHandler;

interface PostProcessDOMHandlerInterface
{
	public function __invoke(\DOMElement $rootElement, \DOMDocument $document);
}
