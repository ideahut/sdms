<?php
namespace Ideahut\sdms\entity;

use Exception;
use ReflectionObject;

use \Doctrine\ORM\Mapping as ORM;

use \Ideahut\sdms\refproxy\Proxy;
use \Ideahut\sdms\repository\Repository;
use \Ideahut\sdms\repository\RepoHandler;


/**
 * @ORM\MappedSuperclass
 */
abstract class Entity 
{
	const ID 		= "id";
	const CREATED 	= "createdAt";
	const UPDATED 	= "updatedAt";
	const VERSION 	= "version";

	public function __construct() {
		$argv = func_get_args();
    	$narg = func_num_args();
    	if ($narg > 0 && is_array($argv[0])) {
    		if (array_keys($argv[0]) !== range(0, count($argv[0]) - 1)) {
    			$obj = new ReflectionObject($this);  			
    			foreach($argv[0] as $key => $value) {
    				if (!$obj->hasProperty($key)) continue;
    				$prop = $obj->getProperty($key);
    				if (!$prop->isPublic()) continue;
    				$prop->setValue($this, $value);
    			}
	    	}    		
    	}
	}


    public function refresh($manager, $logger = null) {
        return (self::create($manager, get_class($this), $logger))->refresh($this);
    }

	public function save($manager, $logger = null) {
        return (self::create($manager, get_class($this), $logger))->save($this);
    }

    public function delete($manager, $logger = null) {
        return (self::create($manager, get_class($this), $logger))->delete($this);
    }

    public static function dao($manager, $logger = null) {
        return self::create($manager, get_called_class(), $logger);
    }

    public static function repo($class, $manager, $logger = null) {
        if (!is_subclass_of($class, Repository::class)) {
            throw new Exception("Class '" . $class . "'' is not sub class of " . Repository::class);   
        }
        $dao = self::create($manager, get_called_class(), $logger);
        return Proxy::newProxyInstance($class, new RepoHandler($class, $dao));
    }

    

    private static function create($manager, $class, $logger = null) {
        $dao = new EntityDao($manager, $class);
        if (null !== $logger) {
            $dao->logger($logger);
        }
        return $dao;
    }

}

