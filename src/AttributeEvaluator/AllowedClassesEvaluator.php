<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class AllowedClassesEvaluator implements AttributeEvaluatorInterface
{
	private $allowedClasses = [];

	public function __construct(array $allowedClasses)
	{
		$this->allowedClasses = $allowedClasses;
	}

	public function __invoke(string $value)
	{
		preg_match_all('/([a-z][a-z0-9_-]*?)\\b/i', $value, $matches, \PREG_PATTERN_ORDER);
		$allowedClasses = array_intersect($matches[0], $this->allowedClasses);

		return (empty($allowedClasses) === false) ? implode(" ", $allowedClasses) : false;
	}
}
