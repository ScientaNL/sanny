<?php

namespace Syslogic\Sanny\AttributeEvaluator;

use League\Uri\Modifiers\Formatter;
use League\Uri\Parser as UriParser;
use Psr\Http\Message\UriInterface;

class UriEvaluator implements AttributeEvaluatorInterface
{
	private $allowedSchemeStrategies;

	const RELATIVE_URI = null;

	public function __construct(array $allowedSchemeStrategies, string $relativeURISchemeStrategy = null)
	{
		if($relativeURISchemeStrategy) {
			$allowedSchemeStrategies[self::RELATIVE_URI] = $relativeURISchemeStrategy;
		}

		$this->allowedSchemeStrategies = $allowedSchemeStrategies;
	}

	public function __invoke(string $value)
	{
		try {
			$uriParser = new UriParser();
			$uriComponents = $uriParser($value);

			if (isset($this->allowedSchemeStrategies[$uriComponents['scheme']]) === false) {
				return false;
			}

			/** @var UriInterface $uri */
			$schemeClassName = $this->allowedSchemeStrategies[$uriComponents['scheme']];
			$uri = $schemeClassName::createFromComponents($uriComponents);

			$formatter = new Formatter();
			$formatter->setQuerySeparator('&amp;');

			return $formatter($uri);
		} catch (\Exception $e) {
			return false;
		}
	}
}
