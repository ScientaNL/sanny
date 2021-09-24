<?php declare(strict_types=1);

use League\Uri\Http;
use PHPUnit\Framework\TestCase;
use Scienta\Sanny\UriSchemeValidator\MailtoValidator;

final class MailToValidatorTest extends TestCase {
	/**
	 * @covers MailtoValidator::isValidUri
	 */
	public function testMailtoValidator(): void
	{
		$validator = new MailtoValidator();

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("mailto:test@test.nl")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("http://www.test.com")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("mailto:test@test.nl#test")
		));

		$this->assertFalse($validator->isValidUri(
			Http::createFromString("mailto:no@valid-email")
		));

		$this->assertTrue($validator->isValidUri(
			Http::createFromString("mailto:valid@valid-email.co.uk?test=355&dada&das")
		));
	}
}
