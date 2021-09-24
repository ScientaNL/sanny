<?php

namespace Scienta\Sanny\AttributeEvaluator;

use League\Uri\Http;
use Scienta\Sanny\Exception\InvalidArgumentException;
use Scienta\Sanny\UriSchemeValidator\UriSchemeValidatorInterface;

class UriEvaluator implements AttributeEvaluatorInterface
{
	private array $allowedSchemeStrategies;
	private bool $autoCorrectWindowsFilePaths;

	const SCHEMELESS_URI = "SCHEMELESS_URI";

	/**
	 * @param array<string, UriSchemeValidatorInterface|true> $allowedSchemeStrategies
	 * @param bool $autoCorrectWindowsFilePaths
	 */
	public function __construct(
		array $allowedSchemeStrategies,
		bool $autoCorrectWindowsFilePaths = false
	) {
		$this->allowedSchemeStrategies = $allowedSchemeStrategies;
		$this->autoCorrectWindowsFilePaths = $autoCorrectWindowsFilePaths;
	}

	public function __invoke(string $value)
	{
		try {
			$uri = Http::createFromString($value);

			if ($uri->getScheme()) {
				if(!isset($this->allowedSchemeStrategies[$uri->getScheme()])) {
					//Auto-correct a Windows file path
					if ($this->autoCorrectWindowsFilePaths && preg_match('%^\\b[a-z]:\\\\[^/:*?"<>|\\r\\n]*$%i', $value)) {
						return $this->__invoke(UriEvaluator::correctWindowsFilePath($value));
					} else {
						return false;
					}
				} else {
					$schemeValidator = $this->allowedSchemeStrategies[$uri->getScheme()];
				}
			} else {
				$schemeValidator = $this->allowedSchemeStrategies[self::SCHEMELESS_URI] ?? false;
			}

			if (!is_bool($schemeValidator) && !($schemeValidator instanceof UriSchemeValidatorInterface)) {
				throw new InvalidArgumentException("Invalid scheme definition provided");
			} elseif (!$schemeValidator
				|| ($schemeValidator instanceof UriSchemeValidatorInterface && !$schemeValidator->isValidUri($uri))
			) {
				return false;
			}

			return str_replace(["&", "%26"], ["&amp;", "&amp;"], (string) $uri);
		} catch (\Exception $e) {
			return false;
		}
	}

	private static function correctWindowsFilePath(string $value): string
	{
		return "file:///" . str_replace("\\", "/", $value);
	}
}
