<?php

namespace Scienta\Sanny\AttributeEvaluator;

class ColorEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		return preg_match('/^(?:\\w+|#{0,1}[a-f0-9]{3,6})$/im', $value)
			? $value : false;
	}
}
