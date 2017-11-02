<?php

namespace Syslogic\Sanny\AttributeEvaluator;

use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Parsing\SourceException;
use Sabberworm\CSS\Rule\Rule;
use Sabberworm\CSS\RuleSet\DeclarationBlock;

class StyleWhiteListEvaluator implements AttributeEvaluatorInterface
{
	private static $outputFormat;

	private $rulesWhitelist = [];
	private $allowImportant = false;

	private $cache = [];

	private $callbacks = [];

	public function __construct(array $rulesWhitelist, bool $allowImportant = false)
	{
		$this->rulesWhitelist = $rulesWhitelist;
		$this->allowImportant = $allowImportant;
	}

	public function __invoke(string $value)
	{
		$value = html_entity_decode($value);

		if (isset($this->cache[$value]) === true) {
			return $this->cache[$value];
		}

		set_error_handler(
			function (int $errno, string $errstr) {
				throw new \Error($errstr, $errno);
			}
		);

		/**
		 * @todo Sabberworm's CSS parser outputs notices. Re-evaluate this dependency
		 */
		try {
			$parser = new Parser("containert { $value }");
			$css = $parser->parse();

			$ruleSets = $css->getAllRuleSets();

			if (count($ruleSets) !== 1) {
				return $this->cache[$value] = false;
			}

			/**
			 * @var DeclarationBlock[] $ruleSets
			 * @var Rule[] $rules
			 */
			$rules = $ruleSets[0]->getRulesAssoc();

			//Call callbacks
			foreach ($this->callbacks as $callback) {
				$callback($rules);
			}

			$allowedRules = [];
			foreach ($rules as $rule) {
				if ($this->ruleIsAllowed($rule) === true) {
					$allowedRules[] = $rule->render(self::getOutputFormat());
				}
			}

			return $this->cache[$value] = (empty($allowedRules) === false)
				? implode(" ", $allowedRules)
				: false;

		} catch (SourceException $e) {
			return $this->cache[$value] = false;
		} catch (\Error $e) {
			return $this->cache[$value] = false;
		} finally {
			restore_error_handler();
		}
	}

	private function ruleIsAllowed(Rule $rule)
	{
		$ruleString = $rule->getRule();

		$whiteListed = false;
		foreach ($this->rulesWhitelist as $item) {

			if ($item === $ruleString) {
				$whiteListed = true;
			} elseif ("*" === substr($item, -1) && strpos($ruleString, substr($item, 0, -1)) === 0) {
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

	public function addCallback(callable $callback)
	{
		$this->callbacks[] = $callback;
	}
}
