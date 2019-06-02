<?php
namespace Ideahut\sdms\util;

use Exception;

use \Ideahut\sdms\Common;

final class SequenceUtil
{
	
	private static $PREFIX = "___SEQ_";


	public static function value($key) {
		$config = self::prepare($key);
		$max 	= isset($config[Common::SETTING_MAX]) ? (int)$config[Common::SETTING_MAX] : 999999;
		$start 	= isset($config[Common::SETTING_START]) ? (int)$config[Common::SETTING_START] : 0;
		$value  = apcu_fetch(self::$PREFIX . $key);
		if ($value === false) {
			$value = $start;
		}
		$value++;
		if ($value > $max) {
			$value = $start;
		}
		apcu_store(self::$PREFIX . $key, $value);
		return $value;
	}

	public static function reset($key) {
		$config = self::prepare($key);
		apcu_delete(self::$PREFIX . $key);
	}

	private static function prepare($key) {
		$settings = Common::getSettings();
		if (!isset($settings[Common::SETTING_SETTINGS])) {
			throw new Exception("Invalid settings");
		}
		$config = $settings[Common::SETTING_SETTINGS];

		if (!isset($config[Common::SETTING_SEQUENCE])) {
			throw new Exception("Setting " . Common::SETTING_SEQUENCE . " is undefined");	
		}
		$config = $config[Common::SETTING_SEQUENCE];

		if (!isset($config[$key])) {
			throw new Exception("Setting " . Common::SETTING_SEQUENCE . " for key '" . $key .  "' is not found");	
		}
		return $config[$key];
	}

}