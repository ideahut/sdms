<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

class count extends base {

	public function execute($method, $args) {
		if ("count" === $method) {
			return $this->dao->count();
		} else if ("countBy" === substr($method, 0, 7)) {
			$filter = $this->filter(substr($method, 7), $args);
			return $this->dao->filter($filter)->count();
		} else {
			throw new Exception("Invalid method, for: " . $this->refclass->name . "::" . $method);
		}
	}

}