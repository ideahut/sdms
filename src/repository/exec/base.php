<?php
namespace Ideahut\sdms\repository\exec;

use Exception;

abstract class base {

	protected $refclass;

	protected $dao;

    public function __construct($refclass, $dao) {
    	$this->refclass = $refclass;
    	$this->dao = $dao;
    }

    protected function filter($method, $args) {
    	$text = $method;
    	$list = [];
    	$text = $this->_filter($text, $list);
    	while(null !== $text) {
    		$text = $this->_filter($text, $list);
    	}
    	if (count($list) > count($args)) {
    		throw new Exception("Invalid arguments number, for: " . $this->refclass->name . "::" . $method);
    	}
    	$result = [];
    	for ($i = 0; $i < count($list); $i++) {
    		if (is_array($args[$i]) && array_keys($args[$i]) === range(0, count($args[$i]) - 1)) {
    			$result[$list[$i] . "__in"] = $args[$i];
    		} else {
    			$result[$list[$i]] = $args[$i];
    		}
    	}
    	return $result;
    }

    private function _filter($text, &$res = []) {
    	if ("" === $text) {
    		return null;
    	}
    	if (substr($text, 0, 3) !== "And" && substr($text, 0, 2) !== "Or") {
    		$text = "And" . $text;
    	}
    	$isAnd  = substr($text, 0, 3) === "And";
    	$text   = substr($text, $isAnd ? 3 : 2);
    	$posAnd = strpos($text, "And");
    	$posOr  = strpos($text, "Or");
		$posCut = 0;
		if ($posAnd !== false && $posOr !== false) {
			if ($posAnd < $posOr) {
				$posCut = $posAnd;
			} else {
				$posCut = $posOr;
			}
		} else if ($posAnd !== false) {
			$posCut = $posAnd;
		} else if ($posOr !== false) {
			$posCut = $posOr;
		} else {
			array_push($res, (!$isAnd ? "or" . $this->dao->delimiter() : "") . strtolower(substr($text, 0, 1)) . substr($text, 1));
			return null;		
		}
		$field = substr($text, 0, $posCut);
		array_push($res, (!$isAnd ? "or" . $this->dao->delimiter() : "") . strtolower(substr($field, 0, 1)) . substr($field, 1));
		return substr($text, $posCut);
    }

    abstract public function execute($method, $args);
}