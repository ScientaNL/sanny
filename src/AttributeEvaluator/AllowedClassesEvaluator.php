<?php

namespace Scienta\Sanny\AttributeEvaluator;

class AllowedClassesEvaluator implements AttributeEvaluatorInterface
{
	private $allowedClasses = [];

	public function __construct(array $allowedClasses)
	{
		$this->allowedClasses = $allowedClasses;
	}

	public function __invoke(string $value)
	{
		$allowedClasses = array_intersect(
			(array)preg_split('/\\s+/i', $value),
			$this->allowedClasses
		);

		return (empty($allowedClasses) === false) ? implode(" ", $allowedClasses) : false;
	}
}
