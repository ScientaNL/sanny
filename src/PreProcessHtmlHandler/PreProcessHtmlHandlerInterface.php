<?php

namespace Syslogic\Sanny\PreProcessHtmlHandler;

interface PreProcessHtmlHandlerInterface
{
	public function __invoke(string $html): string;
}