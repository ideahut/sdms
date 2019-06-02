<?php
namespace Ideahut\sdms\base;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\annotation as IDH;

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
	 * @IDH\Document(ignore=true)
	 */
	public function setApp($app) {
		$this->app = $app;
		return $this;
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */ 
	public function getApp() {
		return $this->app;
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getSettings() {
		return $this->app->getContainer()[Common::SETTING_SETTINGS];
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getLogger() {
		return $this->app->getContainer()[Common::SETTING_LOGGER];
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getCache() {
		return $this->app->getContainer()[Common::SETTING_CACHE];
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getEntityManager() {
		return $this->app->getContainer()[Common::SETTING_ENTITY_MANAGER];
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getDatabase() {
		return $this->app->getContainer()[Common::SETTING_DATABASE];
	}	
	
}