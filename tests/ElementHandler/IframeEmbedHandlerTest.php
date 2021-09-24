<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scienta\Sanny\AttributeEvaluator\UriEvaluator;
use Scienta\Sanny\ElementHandler\IframeEmbedHandler;

/**
 * @covers IframeEmbedHandler
 */
final class IframeEmbedHandlerTest extends TestCase
{
	public function testUriHandler(): void
	{
		$domElement = $this->convertHtmlSnippetToDomNode('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>');

		$handler = new IframeEmbedHandler(new UriEvaluator([]), []);

		$result = $handler(
			$domElement,
			function (\DOMAttr $attribute, DOMElement $element) {
			}
		);

		$this->assertFalse($result);
	}

	public function testRegexStrategyOnAllowedUrl(): void
	{
		$domElement = $this->convertHtmlSnippetToDomNode('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>');

		$handler = new IframeEmbedHandler(
			new UriEvaluator(["https" => true]),
			[
				'www.youtube.com' => '%^(https?:)?//www\\.youtube(?:-nocookie)?\\.com/embed/%i',
			]
		);

		$result = $handler(
			$domElement,
			function (\DOMAttr $attribute, DOMElement $element) {
			}
		);

		$this->assertNull($result);
	}

	public function testRegexStrategyOnDisallowedUrl(): void
	{
		$domElement = $this->convertHtmlSnippetToDomNode('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>');

		$handler = new IframeEmbedHandler(
			new UriEvaluator(["https" => true]),
			[
				'www.tandpastatube.com' => '%^(https?:)?//www\\.tandpastatube(?:-nocookie)?\\.com/embed/%i',
			]
		);

		$result = $handler(
			$domElement,
			function (\DOMAttr $attribute, DOMElement $element) {
			}
		);

		$this->assertFalse($result);
	}

	private function convertHtmlSnippetToDomNode(string $html): DOMElement
	{
		$document = new \DomDocument('1.0', 'UTF-8');
		$document->substituteEntities = false;
		$document->preserveWhiteSpace = false;
		$document->formatOutput = false;

		@$document->loadHTML(sprintf('<?xml encoding="UTF-8"><html><body>%s</body></html>', $html));

		$node = $document->getElementsByTagName("iframe")->item(0);
		if(!($node instanceof DOMElement)) {
			throw new \Scienta\Sanny\Exception\InvalidArgumentException("Could not extract Dom Element");
		}

		return $node;
	}
}
