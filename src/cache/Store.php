<?php
namespace Ideahut\sdms\cache;

class Store
{
	private $value;
	
	private $lifetime;
	
	public function __construct($value, $expiration) {
		$this->value = $value;
		if ($expiration > 0) {
			$this->lifetime = round(microtime(true) * 1000) + $expiration;
		} else {
			$this->lifetime = 0;
		}
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function isExpired() {
		if ($this->lifetime == 0) {
			return false;
		}
		return round(microtime(true) * 1000) > $this->lifetime;
	}
}