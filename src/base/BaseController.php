<?php
namespace Ideahut\sdms\base;

use \Ideahut\sdms\annotation as IDH;

abstract class BaseController extends BaseService
{

	private $config;

	private $args;

	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
        if ($narg === 1) {
    		if ($argv[0] instanceof BaseService) {
    			$this->setApp($argv[0]->getApp());
    			$this->setRequest($argv[0]->getRequest());
    			$this->setAccess($argv[0]->getAccess());
    		} else if ($argv[0] instanceof BaseController) {
    			$this->setApp($argv[0]->getApp());
    			$this->setRequest($argv[0]->getRequest());
    			$this->setAccess($argv[0]->getAccess());
    			$this->setConfig($argv[0]->getConfig());
    			$this->setArgs($argv[0]->getArgs());
    		}
    	}
    }

	/**
	 * @IDH\Document(ignore=true)
	 */
	public function setConfig($config) {
		$this->config = $config;
		return $this;
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * @IDH\Document(ignore=true)
	 */
	public function setArgs($args) {
		$this->args = $args;
		return $this;
	}
	
	/**
	 * @IDH\Document(ignore=true)
	 */
	public function getArgs() {
		return $this->args;
	}	
	
}