<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

class map extends base {

	public function execute($method, $args) {
		$dao    = $this->dao;
		$filter = null;
		$mapkey = null;
		$modarr = $args;
		$themtd = substr($method, 3);
		$posby	= strpos($themtd, "By");
		if (false !== $posby) {
			$mapkey = substr($themtd, 0, $posby);
			$filter = $this->filter(substr($themtd, $posby + 2), $args);
			$modarr = array_slice($args, count($filter));
		} else {
			$mapkey = $themtd;
		}
		if ("" === $mapkey) {
			$mapkey = null;
		} else {
			$mapkey = strtolower(substr($mapkey, 0, 1)) . substr($mapkey, 1);
		}
		$order  = null;
		$start  = null;
		$limit  = null;
		for ($i = 0; $i < count($modarr); $i++) {
			$v = $modarr[$i];
			if (!isset($order) && is_array($v) && array_keys($v) === range(0, count($v) - 1)) {
				$order = $v;
				continue;
			}
			if (!isset($limit) && is_int($v)) {
				$limit = $v;
				continue;
			}
			if (!isset($start) && is_int($v)) {
				$start = $v;
				continue;
			}
		}
		return $this->dao->filter($filter)->order($order)->start($start)->limit($limit)->map($mapkey);
	}

}