<?php

namespace Syslogic\Sanny\AttributeEvaluator;

use League\Uri\Modifiers\Formatter;
use League\Uri\Parser as UriParser;
use League\Uri\Schemes\UriException;
use Psr\Http\Message\UriInterface;

class UriEvaluator implements AttributeEvaluatorInterface
{
	private $allowedSchemeStrategies;

	const RELATIVE_URI = null;

	public function __construct(array $allowedSchemeStrategies)
	{
		$this->allowedSchemeStrategies = $allowedSchemeStrategies;
	}

	public function __invoke(string $value)
	{
		$uriParser = new UriParser();
		$uriComponents = $uriParser($value);

		if(isset($this->allowedSchemeStrategies[$uriComponents['scheme']]) === false) {
			return false;
		}

		try
		{
			/** @var UriInterface $uri */
			$schemeClassName = $this->allowedSchemeStrategies[$uriComponents['scheme']];
			$uri = $schemeClassName::createFromComponents($uriComponents);

			$formatter = new Formatter();
			$formatter->setQuerySeparator('&amp;');

			return $formatter($uri);
		}
		catch(UriException $e) {
			return false;
		}
	}
}
