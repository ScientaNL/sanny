<?php

namespace Scienta\Sanny\AttributeEvaluator;

use PHPUnit\Framework\TestCase;
use Sabberworm\CSS\Value\Size;
use Scienta\Sanny\Exception\UnwantedElementException;

/**
 * @covers StyleWhiteListEvaluator
 */
class StyleWhiteListEvaluatorTest extends TestCase
{
	public function testSimpleWhitelist(): void
	{
		$styleEvaluator = new StyleWhiteListEvaluator(
			[
				'border*',
				'height',
				'padding*',
				'width',
			]
		);

		$this->assertEquals(
			"width: 20px;",
			$styleEvaluator("width: 20px")
		);

		$this->assertEquals(
			"width: 20px; height: 20px;",
			$styleEvaluator("width: 20px; height: 20px;")
		);

		$this->assertEquals(
			"height: 20px; width: 20px;",
			$styleEvaluator("height: 20px; width: 20px;")
		);

		$this->assertEquals(
			"height: 20px;",
			$styleEvaluator("height: 20px; margin: 10px;")
		);

		$this->assertEquals(
			"padding-top: 10px; height: 20px;",
			$styleEvaluator("padding-top: 10px; height: 20px;")
		);
	}

	public function testCallback(): void
	{
		$styleEvaluator = new StyleWhiteListEvaluator(['height', 'width']);

		$styleEvaluator->addCallback(function (array $rules) {
			if ((isset($rules['height']))) {
				throw new UnwantedElementException();
			}
		});

		$this->assertEquals(
			"width: 20px;",
			$styleEvaluator("width: 20px;")
		);

		$this->expectException(UnwantedElementException::class);
		$styleEvaluator("width: 20px; height: 20px;");
	}
}
