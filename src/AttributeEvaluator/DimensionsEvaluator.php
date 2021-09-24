<?php

namespace Scienta\Sanny\AttributeEvaluator;

class DimensionsEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		return (preg_match('/^\\d+(?:\\.\\d+|)\\s{0,2}?(?:em|ex|%|px|cm|mm|in|pt|pc|ch|rem|)$/', $value))
			? $value : false;
	}
}
