<?php

namespace Syslogic\Sanny\PreProcessHtmlHandler;

/**
 * A common mistake seen in WYSIWYG editors is a paragraph in a heading. This is no problem for the browser,
 * but the gerenated code of DOMDocument has visual discrepancies with the unsanitized code.
 *
 * Example:
 * Without: <h1><p>Hello World</h1></p> --> <h1></h1><p>Hello World</p>
 * With: <h1><p>Hello World</h1></p> --> <h1>Hello World</h1>
 */
class CorrectParagraphsInHeadingsHandler implements PreProcessHtmlHandlerInterface
{
	public function __invoke(string $html): string
	{
		return preg_replace('%(<h[1-6]>\\s*?)<p>([^<]*?)</p>(\\s*?</h[1-6]>)%', '\\1\\2\\3', $html);
	}
}
