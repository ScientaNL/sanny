<?php

namespace Scienta\Sanny\PreProcessHtmlHandler;

interface PreProcessHtmlHandlerInterface
{
	public function __invoke(string $html): string;
}
