<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

class delete extends base {

	public function execute($method, $args) {
		if (count($args) < 1) {
			throw new Exception("Invalid arguments number, for: " . $this->refclass->name . "::" . $method);
		}
		if ("delete" === $method) {
			if (!isset($args[0])) {
				return null;
			}
			if (get_class($args[0]) !== $this->dao->entity_class()) {
                throw new Exception("Invalid entity class, for: " . $this->refclass->name . "::" . $method);
            }
			return $this->dao->delete($args[0]);
		} else if ("deleteAll" === $method) {
			if (is_array($args[0]) && array_keys($args[0]) === range(0, count($args[0]) - 1)) {
				$result = [];
				for ($i = 0; $i < count($args[0]); $i++) {
					$entity = $this->dao->delete($args[0][$i]);
					array_push($result, $entity);
				}
				return $result;
			}
			return null;
		} else if ("deleteBy" === substr($method, 0, 8)) {
			$filter = $this->filter(substr($method, 8), $args);
			return $this->dao->filter($filter)->delete();
		} else {
			throw new Exception("Invalid method, for: " . $this->refclass->name . "::" . $method);
		}
	}

}