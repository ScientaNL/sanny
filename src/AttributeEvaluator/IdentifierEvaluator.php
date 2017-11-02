<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class IdentifierEvaluator implements AttributeEvaluatorInterface
{
	public function __invoke(string $value)
	{
		/**
		 * Allow space because people may use it in anchor ids...
		 * Allow percent because people may use spaces in name resulting in a %20
		 */
		return (preg_match('/^[_a-zA-Z0-9,][\\w:.\-, ()%]*$/m', $value))
			? $value : false;
	}
}
