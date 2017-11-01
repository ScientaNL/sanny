<?php

namespace Syslogic\Sanny\PostprocessDOMHandler;

interface PostProcessDOMHandlerInterface
{
	public function __invoke(\DOMElement $rootElement, \DOMDocument $document);
}
