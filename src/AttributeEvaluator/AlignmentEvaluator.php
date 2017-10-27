<?php

namespace Syslogic\Sanny\AttributeEvaluator;

class AlignmentEvaluator extends EnumEvaluator
{
	private $allowedValues = [];

	public function __construct(bool $horizontal = true, bool $vertical = true)
	{
		$allowedValues = [];

		if ($horizontal) {
			$allowedValues = array_merge($allowedValues, ["left", "center", "right", "justify"]);
		}

		if ($vertical) {
			$allowedValues = array_merge($allowedValues, ["baseline", "bottom", "middle", "top"]);
		}

		parent::__construct($allowedValues);
	}
}
