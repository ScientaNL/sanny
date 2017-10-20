<?php

namespace Syslogic\Sanny\AttributeEvaluator;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

class StyleWhiteListEvaluator implements AttributeEvaluatorInterface
{
	private static $outputFormat;

	private $rulesWhitelist = [];
	private $allowImportant = false;

	private $cache = [];

	public function __construct(array $rulesWhitelist, bool $allowImportant = false)
	{
		$this->rulesWhitelist = $rulesWhitelist;
		$this->allowImportant = $allowImportant;
	}

	public function __invoke(string $value)
	{
		if(isset($this->cache[$value]) === true) {
			return $this->cache[$value];
		}

		$parser = new Parser("containert { $value }");
		$css = $parser->parse();
		$ruleSets = $css->getAllRuleSets();

		if (count($ruleSets) !== 1) {
			return false;
		}

		/** @var DeclarationBlock $ruleSet */
		$ruleSet = $ruleSets[0];

		$allowedRules = [];
		/** @var Rule $rule */
		foreach ($ruleSet->getRules() as $rule) {

			if($this->ruleIsAllowed($rule) === true) {
				$allowedRules[] = $rule->render(self::getOutputFormat());
			}
		}

		return $this->cache[$value] = (empty($allowedRules) === false)
			? implode(" ", $allowedRules)
			: false;
	}

	private function ruleIsAllowed(Rule $rule)
	{
		$ruleString = $rule->getRule();

		$whiteListed = false;
		foreach($this->rulesWhitelist as $item) {

			if($item === $ruleString) {
				$whiteListed = true;
			} else if("*" === substr($item, -1) && strpos($ruleString, substr($item, 0, -1)) === 0) {
				$whiteListed = true;
			}
		}

		return ($whiteListed === true && ($this->allowImportant === true || $rule->getIsImportant() === false));
	}

	private static function getOutputFormat(): OutputFormat
	{
		if (self::$outputFormat instanceof OutputFormat === false) {
			self::$outputFormat = new OutputFormat();
		}

		return self::$outputFormat;
	}
}
