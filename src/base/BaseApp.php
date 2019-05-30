<?php
namespace Ideahut\sdms\base;

use \Ideahut\sdms\Common;

abstract class BaseApp
{
	
	private $app;

	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
        if ($narg === 1) {
    		$this->app($argv[0]);
    	}
    }
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function setApp($app) {
		$this->app = $app;
		return $this;
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */ 
	public function getApp() {
		return $this->app;
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getSettings() {
		return $this->app->getContainer()[Common::SETTING_SETTINGS];
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getLogger() {
		return $this->app->getContainer()[Common::SETTING_LOGGER];
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getCache() {
		return $this->app->getContainer()[Common::SETTING_CACHE];
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getEntityManager() {
		return $this->app->getContainer()[Common::SETTING_ENTITY_MANAGER];
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getDatabase() {
		return $this->app->getContainer()[Common::SETTING_DATABASE];
	}	
	
}