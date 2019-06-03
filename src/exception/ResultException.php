<?php
namespace Ideahut\sdms\exception;

use \Exception;

use \Ideahut\sdms\object\Result;

class ResultException extends Exception
{
	private $result;

	public function __construct(Result $result) {
        $this->result = $result;
    }

    public function getResult() {
    	return $this->result;
    }

}