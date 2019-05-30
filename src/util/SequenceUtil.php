<?php
namespace Ideahut\sdms\util;

use Exception;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\base\BaseApp;

final class SequenceUtil
{
	
	private static $PREFIX = "___SEQ_";


	public static function value($settings, $key) {
		$config = self::prepare($settings, $key);
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

	public static function reset($settings, $key) {
		$config = self::prepare($settings, $key);
		apcu_delete(self::$PREFIX . $key);
	}

	private static function prepare($settings, $key) {
		if (!isset($settings)) {
			throw new Exception("Parameter 'setting' is required");
		}
		$config = $settings;
		if ($config instanceof BaseApp) {
			$config = $config->getSettings();			
		}
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