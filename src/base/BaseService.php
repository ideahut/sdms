<?php
namespace Ideahut\sdms\base;

abstract class BaseService extends BaseApp
{

	private $request;
	
	private $access;
	
	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
        if ($narg === 1) {
    		if ($argv[0] instanceof BaseApp) {
    			$this->setApp($argv[0]->getApp());
    		} else if ($argv[0] instanceof BaseService || $argv[0] instanceof BaseController) {
    			$this->setApp($argv[0]->getApp());
    			$this->setRequest($argv[0]->getRequest());
    			$this->setAccess($argv[0]->getAccess());
    		}
    	}
    }

    /**
	 * @DOCUMENT(ignore=true)
	 */
	public function setRequest($request) {
		$this->request = $request;
		return $this;
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getRequest() {
		return $this->request;
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function setAccess($access) {
		$this->access = $access;
		return $this;
	}
	
	/**
	 * @DOCUMENT(ignore=true)
	 */
	public function getAccess() {
		return $this->access;
	}
}