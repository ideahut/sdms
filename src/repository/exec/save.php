<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

class save extends base {

	public function execute($method, $args) {
		if (count($args) < 1) {
			throw new Exception("Invalid arguments number, for: " . $this->refclass->name . "::" . $method);
		}
		if ("save" === $method) {			
			if (get_class($args[0]) !== $this->dao->entity_class()) {
                throw new Exception("Invalid entity class, for: " . $this->refclass->name . "::" . $method);
            }
            return $this->dao->save($args[0]);
		} else if ("saveAll" === substr($method, 0, 7)) {
			if (is_array($args[0]) && array_keys($args[0]) === range(0, count($args[0]) - 1)) {
				$result = [];
				for ($i = 0; $i < count($args[0]); $i++) {
					$entity = $this->dao->save($args[0][$i]);
					array_push($result, $entity);
				}
				return $result;
			} else {
				throw new Exception("Array arguments is required, for: " . $this->refclass->name . "::" . $method);	
			}
		} else {
			throw new Exception("Invalid method, for: " . $this->refclass->name . "::" . $method);
		}
	}

}