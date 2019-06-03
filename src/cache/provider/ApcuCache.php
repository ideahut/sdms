<?php
namespace Ideahut\sdms\cache\provider;

use \Ideahut\sdms\cache\Cache;
use \Ideahut\sdms\cache\Store;

/**
 * Cache menggunakan extention APC (Untuk PHP 5.3+ pakai yang APCU)
 * Extention ini tidak default di php, sehingga perlu diinstall manual.
 * Tambahan konfigurasi di php.ini:
 * 	extension=php_apcu.dll
 *	apc.enabled=1
 *	apc.shm_size=32M
 *	apc.ttl=0
 *	apc.enable_cli=1
 *	apc.serializer=php
 *
 */
class ApcuCache extends Cache 
{

	private $unlimited = false;

	public function __construct(array $groups, array $parameters = []) {
		if (!extension_loaded('apcu')) {
			throw new \Exception("Extention 'apcu' is not loaded.");			
		}
        parent::__construct($groups, $parameters);
        if (isset($this->parameters[Cache::UNLIMITED])) {
            $this->unlimited = strtolower(strval($this->parameters[Cache::UNLIMITED]));
            $this->unlimited = "true" === $this->unlimited || "1" === $this->unlimited;
        }
    }
	
	protected function doGet($group, $key) {
	    $ckey = serialize($key);
	    $store = apcu_fetch($group . "__" . $ckey);
	    if ($store != null && !$store->isExpired()) {
	        return $store->getValue();
	    }
	    apcu_delete($group . "__" . $ckey);
	    $list = $this->getGroupKeys($group);
	    unset($list[$ckey]);
	    $this->putGroupKeys($group, $list);
	    return null;
	}
	
	protected function doPut($group, $key, $value) {
	    $ckey 		= serialize($key);
	    $config 	= $this->groups[$group];
	    $expiration	= $config[Cache::EXPIRATION];
	    
	    $store = new Store($value, $expiration);
	    apcu_store($group . "__" . $ckey, $store);

	    $list = $this->getGroupKeys($group);
		$found = array_search($ckey, $list);
		if ($found === false) {
		    array_push($list, $ckey);
		}

	    if ($this->unlimited === false) {
		    $limit = $config['limit'];		    
		    $size = count($list);
		    if ($size > $limit) {
		        $diff = $size - $limit;
		        for ($i = 0; $i < $diff; $i++) {
		            $rkey = $list[0];
		            apcu_delete($group . "__" . $rkey);
		            array_splice($list, 0, 1);
		        }	        
		    }		    
		}
		$this->putGroupKeys($group, $list);
	}
	
	protected function doRemove($group, $key) {
	    $ckey = serialize($key);
	    apcu_delete($group . "__" . $ckey);
	    
	    $list = $this->getGroupKeys($group);	    
	    $found = array_search($ckey, $list);
	    if ($found !== false) {
	        array_splice($list, $found);
	    }
	    $this->putGroupKeys($group, $list);	    
	}
	
	protected function doClear($group) {
	    $list = $this->getGroupKeys($group);
	    foreach($list as $ckey) {
	        apcu_delete($group . "__" . $ckey);
	    }
	    $this->putGroupKeys($group, array());
	}

	protected function doKeys($group) {
		$list = $this->getGroupKeys($group);
		return $list;
	}
	


	
	
	private function getGroupKeys($group) {
	    $result = apcu_fetch(Cache::PREFIX . $group);
	    if ($result === false) {
	        $result = array();
	    }
	    return $result;
	}
	
	private function putGroupKeys($group, $keys) {
	    apcu_store(Cache::PREFIX . $group, $keys);
	}
	
}