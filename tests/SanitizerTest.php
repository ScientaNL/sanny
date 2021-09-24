<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scienta\Sanny\AttributeEvaluator\UriEvaluator;
use Scienta\Sanny\ElementHandler\IframeEmbedHandler;
use Scienta\Sanny\SanitizationConfig;
use Scienta\Sanny\Sanitizer;
use Scienta\Sanny\UriSchemeValidator\MailtoValidator;
use Scienta\Sanny\UriSchemeValidator\TelValidator;

/**
 * @covers Sanitizer
 */
final class SanitizerTest extends TestCase
{
	public function testIframes(): void
	{
		$config = new SanitizationConfig();
		$config->addElement(
			'iframe',
			SanitizationConfig::ELEMENT_CUSTOM,
			new IframeEmbedHandler(
				new UriEvaluator(["https" => true, UriEvaluator::SCHEMELESS_URI => true]),
				[
					'www.youtube.com' => '%^(https?:)?//www\\.youtube(?:-nocookie)?\\.com/embed/%i',
				]
			)
		);

		$sanitizer = new Sanitizer($config);
		$this->assertEquals(
			'<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
			$sanitizer->sanitize('<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>')
		);

		$this->assertEquals(
			'',
			$sanitizer->sanitize('<iframe src="https://www.tandpastatube.com/embed/dQw4w9WgXcQ"></iframe>')
		);

		$this->assertEquals(
			'',
			$sanitizer->sanitize('<iframe src="http://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>')
		);

		$this->assertEquals(
			'<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
			$sanitizer->sanitize('<iframe src="//www.youtube.com/embed/dQw4w9WgXcQ"></iframe>')
		);
	}

	public function testMissingSchemeAnchor(): void
	{
		$config = new SanitizationConfig();
		$config->addElement('a');
		$config->addAttribute('href', new UriEvaluator([
		]), ['a', 'area']);

		$sanitizer = new Sanitizer($config);
		$this->assertEquals(
			'<a>Blabla</a>',
			$sanitizer->sanitize('<a href="https://www.tets.com">Blabla</a>',)
		);
	}

	public function testHttpsAnchor(): void
	{
		$config = new SanitizationConfig();
		$config->addElement('a');
		$config->addAttribute(
			'href',
			new UriEvaluator([
				"http" => true,
				"https" => true
			]),
			['a', 'area']
		);

		$sanitizer = new Sanitizer($config);
		$this->assertEquals(
			'<a href="https://www.tets.com">blabla</a>',
			$sanitizer->sanitize('<a href="https://www.tets.com">blabla</a>')
		);
	}

	public function testTelAnchor(): void
	{
		$config = new SanitizationConfig();
		$config->addElement('a');
		$config->addAttribute('href', new UriEvaluator([
			"tel" => new TelValidator()
		]), ['a', 'area']);

		$sanitizer = new Sanitizer($config);
		$this->assertEquals(
			'<a href="tel:+31(0)341700255">Call me, maybe</a>',
			$sanitizer->sanitize('<a href="tel:+31(0)341700255">Call me, maybe</a>')
		);
	}

	public function testMailtoAnchor(): void
	{
		$config = new SanitizationConfig();
		$config->addElement('a');
		$config->addAttribute('href', new UriEvaluator([
			"mailto" => new MailtoValidator()
		]), ['a']);

		$sanitizer = new Sanitizer($config);
		$this->assertEquals(
			'<a href="mailto:test@test.nl"></a>',
			$sanitizer->sanitize('<a href="mailto:test@test.nl"></a>')
		);

		$this->assertEquals(
			'<a></a>',
			$sanitizer->sanitize('<a href="mailto:test@testnl"></a>')
		);
	}
}
