<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Scienta\Sanny\AttributeEvaluator\UriEvaluator;
use Scienta\Sanny\UriSchemeValidator\ImageDataValidator;
use Scienta\Sanny\UriSchemeValidator\MailtoValidator;
use Scienta\Sanny\UriSchemeValidator\TelValidator;

/**
 * @covers UriEvaluator
 */
final class UriEvaluatorTest extends TestCase
{
	public function testHttps(): void
	{
		$uriEvaluator = new UriEvaluator([
			"https" => true,
		]);

		$this->assertEquals(
			"https://www.scienta.nl",
			$uriEvaluator("https://www.scienta.nl")
		);

		$this->assertFalse(
			$uriEvaluator("//www.scienta.nl")
		);
	}

	public function testSchemeless(): void
	{

		$uriEvaluator = new UriEvaluator([
			UriEvaluator::SCHEMELESS_URI => true
		]);

		$this->assertEquals(
			"//www.scienta.nl",
			$uriEvaluator("//www.scienta.nl")
		);
	}

	public function testInvalidProtocol(): void
	{
		$uriEvaluator = new UriEvaluator([]);

		$this->assertFalse(
			$uriEvaluator("https://www.scienta.nl")
		);

		$uriEvaluator = new UriEvaluator(["ftp" => true]);

		$this->assertFalse(
			$uriEvaluator("https://www.scienta.nl")
		);
	}

	public function testMailto(): void
	{
		$uriEvaluator = new UriEvaluator([
			"mailto" => new MailtoValidator()
		]);

		$this->assertEquals(
			"mailto:jaap@vermeer.nl",
			$uriEvaluator("mailto:jaap@vermeer.nl")
		);

		$this->assertFalse(
			$uriEvaluator("mailto:jaap@vermeernl")
		);
	}

	public function testRelative(): void
	{
		$uriEvaluator = new UriEvaluator([
			UriEvaluator::SCHEMELESS_URI => true
		]);

		$this->assertEquals(
			"a/b/c",
			$uriEvaluator("a/b/c")
		);

		$uriEvaluator = new UriEvaluator([
			"http" => true
		]);

		$this->assertFalse(
			$uriEvaluator("a/b/c")
		);
	}

	public function testWindowsFilePaths(): void
	{
		// Normal flow
		$uriEvaluator = new UriEvaluator([
			"file" => true
		], true);

		$this->assertEquals(
			"file:///c:/windows/test.txt",
			$uriEvaluator("c:\\windows\\test.txt",)
		);

		// Toggle not enabled
		$uriEvaluator = new UriEvaluator([
			"file" => true
		], false);

		$this->assertFalse(
			$uriEvaluator("c:\\windows\\test.txt",)
		);

		// File protocol not added
		$uriEvaluator = new UriEvaluator([
		], true);

		$this->assertFalse(
			$uriEvaluator("c:\\windows\\test.txt",)
		);
	}

	public function testTel(): void
	{
		$uriEvaluator = new UriEvaluator([
			"tel" => new TelValidator()
		]);

		$this->assertEquals(
			"tel:+31(0)341-700255",
			$uriEvaluator("tel:+31(0)341-700255")
		);
	}

	public function testBrokenUri(): void
	{
		$uriEvaluator = new UriEvaluator([]);

		$this->assertFalse(
			$uriEvaluator("data:image/gifbase64abc")
		);
	}

	public function testDataImage(): void
	{
		// Valid data
		$uriEvaluator = new UriEvaluator([
			"data" => new ImageDataValidator()
		]);

		$this->assertEquals(
			ImageDataValidatorTest::gifImg,
			$uriEvaluator(ImageDataValidatorTest::gifImg)
		);

		// Invalid data
		$uriEvaluator = new UriEvaluator([
			"data" => new ImageDataValidator()
		]);

		$this->assertFalse(
			$uriEvaluator(ImageDataValidatorTest::brokenImg)
		);
	}


}
