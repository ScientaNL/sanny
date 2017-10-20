<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class EnumEvaluator implements AttributeEvaluatorInterface
{
	private $allowedValues = [];

	public function __construct(array $allowedValues)
	{
		$this->allowedValues = $allowedValues;
	}

	public function __invoke(string $value)
	{
		return in_array(trim($value), $this->allowedValues) ? $value : false;
	}
}
