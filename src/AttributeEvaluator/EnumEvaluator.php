<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class EnumEvaluator implements AttributeEvaluatorInterface
{
	private $allowedValues = [];

	public function __construct(array $allowedValues)
	{
		$this->allowedValues = array_map(
			function ($value) {
				return strtolower($value);
			},
			$allowedValues
		);
	}

	public function __invoke(string $value)
	{
		return in_array(strtolower(trim($value)), $this->allowedValues) ? $value : false;
	}
}
