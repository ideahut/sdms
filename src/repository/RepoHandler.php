<?php
namespace Ideahut\sdms\repository;

use Exception;
use ReflectionClass;

use \Ideahut\sdms\base\BaseApp;
use \Ideahut\sdms\refproxy\InvocationHandler;

class RepoHandler implements InvocationHandler 
{
    
    private $refclass;

	private $dao;

	public function __construct($class, $dao) {
    	$this->refclass = new ReflectionClass($class);
    	$this->dao = $dao;
    }

    public function invoke($proxy, $method, $args) {
    	if (!$this->refclass->hasMethod($method)) {
    		throw new Exception("Method is not found, for: " . $this->refclass->name . "::" . $method);
    	}
    	$this->dao->reset();
    	if ($this->refclass->hasMethod("__dao__")) {
    		$mtddao = $this->refclass->getMethod("__dao__");
    		$mtddao->setAccessible(true);
    		$mtddao->invoke($proxy, $this->dao);
    	}
    	$refmethod = $this->refclass->getMethod($method);
        /*
        $params = $refmethod->getParameters();
        $nargs  = count($params);
        for ($i = count($params) - 1; $i >= 0; $i--) {
            if (!$params[$i]->isDefaultValueAvailable()) {
                break;
            } else {
                $nargs = $nargs - 1;
            }
        }
        if (count($args) < $nargs) {
            throw new Exception("Invalid arguments number, for: " . $this->refclass->name . "::" . $method);
        }
    	*/
        if ($refmethod->isAbstract()) {
    		$target = substr($method, 0, 3);
	    	if ("get" === $target || "map" === $target) {
	    		return $this->execute($target, $method, $args);
	    	}

	    	$target = substr($method, 0, 4);
	    	if ("save" === $target || "find" === $target) {
	    		return $this->execute($target, $method, $args);
	    	}

	    	$target = substr($method, 0, 5);
	    	if ("count" === $target) {
	    		return $this->execute($target, $method, $args);
	    	}

	    	$target = substr($method, 0, 6);
	    	if ("delete" === $target) {
	    		return $this->execute($target, $method, $args);
	    	}
	    	throw new Exception("Method is not supported, for: " . $this->refclass->name . "::" . $method);
    	} else {
    		return $refmethod->invoke($proxy, $args);
    	}
    }

    private function execute($target, $method, $args) {
    	$cls = new ReflectionClass(__NAMESPACE__ . "\\exec\\" . $target);
		$ins = $cls->newInstance($this->refclass, $this->dao);
		return $ins->execute($method, $args);
    }
}