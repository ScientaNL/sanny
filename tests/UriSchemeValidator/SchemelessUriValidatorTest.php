<?php declare(strict_types=1);

use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Scienta\Sanny\UriSchemeValidator\SchemelessUriValidator;

final class SchemelessUriValidatorTest extends TestCase {
	/**
	 * @covers SchemelessUriValidator::isValidUri
	 */
	public function testSchemelessUriValidator(): void
	{
		$validator = new SchemelessUriValidator();

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("//wwww.test.com")
		));

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("a/b/c/d")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("http://www.google.com")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("a:/test/das")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("a:\\test\\das")
		));
	}
}
