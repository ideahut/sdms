<?php
namespace Ideahut\sdms\cache;

use Exception;
use ReflectionClass;

abstract class Cache
{
	const PROVIDER		= "provider";
	const GROUP			= "group";
	const PARAMETER		= "parameter";
	const LIMIT			= "limit";
	const UNLIMITED		= "unlimited";
	const EXPIRATION	= "expiration";
	const NULLABLE		= "nullable";

	const PREFIX		= "__KEYS__";

	protected $parameters;
	protected $groups;
	
	public function __construct(array $groups, array $parameters = []) {
	    $this->parameters 	= $parameters;
	    $this->groups 		= $groups;
	}

	public function groups() {
		return $this->groups;
	}

	public function keys($group) {
		return $this->doKeys($group);
	}
	
	public function get($group, $key, callable $callback = null, array $args = null) {		
	    if (!isset($this->groups[$group])) {
			throw new Exception("Cache group is not registered, for: $group");
		}
		$value = $this->doGet($group, $key);
		if ($value != null) {
			if ($value instanceof Nullable) {
				return null;
			}
			return $value;
		}
		if ($callback != null) {
		    $value = $callback($args);
		    $this->put($group, $key, $value);
		}
		return $value;		
	}
	
	public function put($group, $key, $value) {
	    if (!isset($this->groups[$group])) {
	        return false;
	    }
	    if ($value != null) {
	        $this->doPut($group, $key, $value);
	    } else {
	        if ($this->groups[$group][self::NULLABLE] === 1 || $this->groups[$group][self::NULLABLE] === true) {
	            $this->doPut($group, $key, new Nullable());
	        } else {
	            return false;
	        }
	    }
	    return $value;
	}
	
	public function remove($group, $key) {
	    if (!isset($this->groups[$group])) {
			return;
		}
		return $this->doRemove($group, $key);
	}
	
	public function clear($group) {
	    if (!isset($this->groups[$group])) {
			return;
		}
		return $this->doClear($group);
	}
	
	
	abstract protected function doGet($group, $key);
	
	abstract protected function doPut($group, $key, $value);
	
	abstract protected function doRemove($group, $key);
	
	abstract protected function doClear($group);

	abstract protected function doKeys($group);
	
	
	// Untuk membuat instance baru, tergantung dari class provider yang digunakan
	// Lihat di settings.php di bagian 'cache'.
	public static function create(array $config) 
	{
		$provider = $config[self::PROVIDER];
		$cls = new ReflectionClass($provider);
		return $cls->newInstanceArgs(array($config[self::GROUP], isset($config[self::PARAMETER]) ? $config[self::PARAMETER] : []));
	}
	
}