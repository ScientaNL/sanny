<?php

namespace Syslogic\Sanny\AttributeEvaluator;

interface AttributeEvaluatorInterface
{
	public function __invoke(string $value);
}
