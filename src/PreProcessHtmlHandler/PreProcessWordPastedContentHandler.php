<?php

namespace Syslogic\Sanny\PreProcessHtmlHandler;

/**
 * Stub processing HTML pasted from Microsoft Word
 */
class PreProcessWordPastedContentHandler implements PreProcessHtmlHandlerInterface
{
	public function __invoke(string $html): string
	{
		return str_replace(["<o:p>", "</o:p>"], ["<span>", "</span>"], $html);
	}
}
