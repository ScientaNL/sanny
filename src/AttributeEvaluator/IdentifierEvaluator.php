<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class IdentifierEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		//Allow space because people may use it in anchor ids...
		return (preg_match('/^[_a-zA-Z0-9,][\\w:.\-, ()]*$/m', $value))
			? $value : false;
	}
}
