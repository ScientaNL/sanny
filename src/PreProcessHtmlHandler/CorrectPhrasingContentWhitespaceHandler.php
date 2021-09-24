<?php

namespace Scienta\Sanny\PreProcessHtmlHandler;

/**
 * PHP's DOMDocument strips a single whitespace in several phrasing content elements.
 * This handler corrects for this by adding a non breaking space
 *
 * Example:
 * Without: <sup> </sup> --> <sup></sup>
 * With: <sup> </sup> --> <sup>&nbsp;</sup>
 */
class CorrectPhrasingContentWhitespaceHandler implements PreProcessHtmlHandlerInterface
{
	public function __invoke(string $html): string
	{
		$tagNames = ["mark", "math", "output", "progress", "sub", "sup", "time", "wbr"];
		$html = preg_replace(
			'%> </('.implode("|", $tagNames).')>%',
			">\u{00A0}</\\1>",
			$html
		);

		return $html;
	}
}
