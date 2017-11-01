<?php

namespace Syslogic\Sanny\AttributeEvaluator;

use League\Uri\Modifiers\Formatter;
use League\Uri\Parser as UriParser;
use Psr\Http\Message\UriInterface;

class UriEvaluator implements AttributeEvaluatorInterface
{
	/** @var UriParser */
	private $uriParser;
	private $allowedSchemeStrategies;
	private $autoCorrectWindowsFilePaths = false;

	const RELATIVE_URI = null;

	public function __construct(
		array $allowedSchemeStrategies,
		string $relativeURISchemeStrategy = null,
		bool $autoCorrectWindowsFilePaths = false
	)
	{
		if ($relativeURISchemeStrategy) {
			$allowedSchemeStrategies[self::RELATIVE_URI] = $relativeURISchemeStrategy;
		}

		$this->uriParser = new UriParser();

		$this->allowedSchemeStrategies = $allowedSchemeStrategies;
		$this->autoCorrectWindowsFilePaths = $autoCorrectWindowsFilePaths;
	}

	public function __invoke(string $value)
	{
		try {
			$uriParser = $this->uriParser;
			$uriComponents = $uriParser($value);

			// Input could be uppercase, so cast it to lower
			$uriComponents['scheme'] = (is_string($uriComponents['scheme']) === true)
				? strtolower($uriComponents['scheme']) : $uriComponents['scheme'];

			if (isset($this->allowedSchemeStrategies[$uriComponents['scheme']]) === false) {

				if (preg_match('%^\\b[a-z]:\\\\[^/:*?"<>|\\r\\n]*$%i', $value)) {
					return $this->__invoke($this->correctWindowsFilePath($value));
				} else {
					return false;
				}
			}

			/**
			 * Fix ampersands in the path component. PHP league will unescape them,
			 * but DOMDocument does not like an unescaped value
			 */
			$uriComponents['path'] = str_replace(["&", "%26"], ["&amp;", "&amp;"], $uriComponents['path']);

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

	public function getUriParser(): UriParser
	{
		return $this->uriParser;
	}

	private function correctWindowsFilePath(string $value): string
	{
		return "file:///".str_replace("\\", "/", $value);
	}
}
