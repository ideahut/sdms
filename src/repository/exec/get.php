<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

class get extends base {

	public function execute($method, $args) {
		if ("get" === $method) {
			if (count($args) < 1) {
				throw new Exception("Invalid arguments number, for: " . $this->refclass->name . "::" . $method);
			}
			return $this->dao->pk($args[0])->get();
		} else if ("getBy" === substr($method, 0, 5)) {
			$filter = $this->filter(substr($method, 5), $args);
			return $this->dao->filter($filter)->get();
		} else {
			throw new Exception("Invalid method, for: " . $this->refclass->name . "::" . $method);
		}
	}

}