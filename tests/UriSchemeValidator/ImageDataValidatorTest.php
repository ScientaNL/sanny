<?php declare(strict_types=1);

use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Scienta\Sanny\UriSchemeValidator\ImageDataValidator;

/**
 * @covers ImageDataValidator
 */
final class ImageDataValidatorTest extends TestCase
{
	const gifImg = "data:image/gif;base64,R0lGODlhEAAQAMQAAORHHOVSKudfOulrSOp3WOyDZu6QdvCchPGolfO0o/XBs/fNwfjZ0frl3/zy7////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAkAABAALAAAAAAQABAAAAVVICSOZGlCQAosJ6mu7fiyZeKqNKToQGDsM8hBADgUXoGAiqhSvp5QAnQKGIgUhwFUYLCVDFCrKUE1lBavAViFIDlTImbKC5Gm2hB0SlBCBMQiB0UjIQA7";
	const brokenImg = "data:image/gif;base64,dGV0cw==";

	public function testData(): void
	{
		$validator = new ImageDataValidator();

		$this->assertTrue($validator->isValidUri(
			Http::createFromString(self::gifImg)
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("http://www.test.nl")
		));
	}

	public function testMimeTypeCheck(): void
	{
		$validator = new ImageDataValidator([]);
		$this->assertFalse($validator->isValidUri(
			Http::createFromString(self::gifImg)
		));

		$validator = new ImageDataValidator(["image/gif"]);
		$this->assertTrue($validator->isValidUri(
			Http::createFromString(self::gifImg)
		));

		$validator = new ImageDataValidator(["image/jpeg"]);
		$this->assertFalse($validator->isValidUri(
			Http::createFromString(self::gifImg)
		));

		$validator = new ImageDataValidator();
		$this->assertFalse($validator->isValidUri(
			Http::createFromString(self::brokenImg)
		));
	}
}
