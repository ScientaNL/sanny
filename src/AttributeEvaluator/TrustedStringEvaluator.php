<?php

namespace Scienta\Sanny\AttributeEvaluator;

class TrustedStringEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		return $value;
	}
}
