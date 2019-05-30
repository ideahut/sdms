<?php
namespace Ideahut\sdms\cache\provider;

use \Ideahut\sdms\cache\Cache;

/**
 * 
 * Menggunakan Predis
 * composer require predis/predis
 *
 */
class RedisCache extends Cache {
    
    private $client;
    private $unlimited = false;
    
    public function __construct(array $groups, array $parameters = []) {
        parent::__construct($groups, $parameters);
        if (isset($this->parameters[Cache::UNLIMITED])) {
            $this->unlimited = strtolower(strval($this->parameters[Cache::UNLIMITED]));
            $this->unlimited = "true" === $this->unlimited || "1" === $this->unlimited;
        }        
        $this->client = new \Predis\Client($this->parameters);
    }
    
    protected function doGet($group, $key) {
        $ckey = serialize($key);
        $value = $this->client->get($group . "__" . $ckey);
        if ($value === null) {
            $list = $this->getGroupKeys($group);
            unset($list[$ckey]);
            $this->putGroupKeys($group, $list);
        } else {
            $value = unserialize($value);
        }
        return $value;        
    }
    
    protected function doPut($group, $key, $value) {
        $ckey       = serialize($key);
        $config     = $this->groups[$group];
        $expiration = $config[Cache::EXPIRATION];
        
        if ($expiration > 0) {
            $this->client->setex($group . "__" . $ckey, $expiration, serialize($value));
        } else {
            $this->client->set($group . "__" . $ckey, serialize($value));
        }
        
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
                    $this->client->del($group . "__" . $rkey);
                    array_splice($list, 0, 1);
                }
            }            
        }
        $this->putGroupKeys($group, $list);
    }
    
    protected function doRemove($group, $key) {
        $ckey = serialize($key);
        $this->client->del($group . "__" . $ckey);
        $list = $this->getGroupKeys($group);
        unset($list[$ckey]);
        $this->putGroupKeys($group, $list);        
    }
    
    protected function doClear($group) {
        $list = $this->getGroupKeys($group);
        foreach($list as $ckey) {
            $this->client->del($group . "__" . $ckey);
        }
        $this->putGroupKeys($group, array());
    }
    
    protected function doKeys($group) {
        $list = $this->getGroupKeys($group);
        return $list;
    }


    
    private function getGroupKeys($group) {
        $result = $this->client->get(Cache::PREFIX . $group);
        if ($result === null) {
            $result = array();
        } else {
            $result = unserialize($result);
        }
        return $result;
    }
    
    private function putGroupKeys($group, $keys) {
        $this->client->set(Cache::PREFIX . $group, serialize($keys));
    }
    
}