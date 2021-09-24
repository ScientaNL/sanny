<?php

namespace Scienta\Sanny\AttributeEvaluator;

interface AttributeEvaluatorInterface
{
	public function __invoke(string $value);
}
