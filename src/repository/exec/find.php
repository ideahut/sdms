<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

use \Ideahut\sdms\object\Page;

class find extends base {

	public function execute($method, $args) {
		$dao    = $this->dao;
		$filter = null;
		$modarr = null;
		$isOne	= true;
		if ("findAllBy" === substr($method, 0, 9)) {
			$filter = $this->filter(substr($method, 9), $args);
			$modarr = array_slice($args, count($filter));
			$isOne  = false;
		} else if ("findAll" === $method) {
			$modarr = $args;
			$isOne  = false;
		} else if ("findBy" === substr($method, 0, 6)) {
			$filter = $this->filter(substr($method, 6), $args);
			$modarr = array_slice($args, count($filter));
		} else if ("find" === $method) {
			$modarr = $args;
		} else {
			throw new Exception("Invalid method, for: " . $this->refclass->name . "::" . $method);
		}

		$order  = null;
		$page   = null;
		$start  = null;
		$limit  = null;
		for ($i = 0; $i < count($modarr); $i++) {
			$v = $modarr[$i];
			if (!$isOne && !isset($page) && $v instanceof Page) {
				$page = $v;
				continue;
			}
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
		$dao->filter($filter)->order($order);
		if ($isOne) {
			$dao->limit(1);
		} else {
			if (isset($page)) {
				$dao->page($page);
			} else if (isset($limit)) {
				$dao->limit($limit)->start($start);
			}
		}
		$result = $dao->select();
		if ($isOne) {
			if (count($result) > 0) {
				return $result[0];
			}
			return null;
		}
		return $result;
	}

}