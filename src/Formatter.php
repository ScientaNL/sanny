<?php

namespace Syslogic\Sanny;

class Formatter
{
	private $tidyConfig = [];
	private $configCachePath;

	private static $autoBoolable = ["merge-divs", "merge-spans", "show-body-only", "indent", "output-bom"];

	public function __construct(array $tidyConfig = [], string $configCachePath = null)
	{
		$this->tidyConfig = $tidyConfig;
		$this->configCachePath = $configCachePath ?: sys_get_temp_dir();
	}

	public function format(string $contents): string
	{
		return (new \tidy())->repairString(
			$contents,
			$this->getTidyConfig(),
			'utf8'
		);
	}

	/**
	 * Tidy Plugin has values they call AutoBool.
	 * Unfortunately they are not settable by PHP, so write the config to a file if there is an value set to auto
	 */
	private function getTidyConfig()
	{
		$hasAutoBool = false;
		foreach (self::$autoBoolable as $property) {
			if (isset($this->tidyConfig[$property]) && $this->tidyConfig[$property] === "auto") {
				$hasAutoBool = true;
				break;
			}
		}

		if ($hasAutoBool === false) {
			return $this->tidyConfig;
		}

		return $this->createTidyConfig();
	}

	private function createTidyConfig()
	{
		$lines = [];
		foreach ($this->tidyConfig as $key => $value) {
			$lines[] = sprintf("%s: %s", $key, is_bool($value) ? (int)$value : (string)$value);
		}

		$config = implode(\PHP_EOL, $lines).\PHP_EOL;
		$configHash = md5($config);
		$configPath = $this->configCachePath.DIRECTORY_SEPARATOR."tidy-config-".$configHash;

		if (realpath($configPath) === false) {
			file_put_contents($configPath, $config);
		}

		return $configPath;
	}
}
