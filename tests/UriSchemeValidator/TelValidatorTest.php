<?php declare(strict_types=1);

use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Scienta\Sanny\UriSchemeValidator\TelValidator;

final class TelValidatorTest extends TestCase {
	/**
	 * @covers TelValidator::isValidUri
	 */
	public function testMailtoValidator(): void
	{
		$validator = new TelValidator();

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("tel:+31(0)341 - 700 255")
		));

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("tel:+31341700255")
		));
	}
}
