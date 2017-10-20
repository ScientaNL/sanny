<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class NumberEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		return is_numeric($value) ? $value : false;
	}
}
