<?php
namespace Ideahut\sdms\object;

use Ideahut\sdms\annotation as IDH;

/**
 * @IDH\Format
 */
class CodeMsg
{

	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
		if ($narg > 0) {
			$this->code = $argv[0];
		}
		if ($narg > 1) {
			$this->message = $argv[1];
		}
		if ($narg > 2) {
			$this->object = $argv[2];
		}
		if ($narg > 3) {
			$this->field = $argv[3];
		}
    }


    /**
     * @IDH\Document(description="Code", type="string / integer")
     */
	public $code;
	
	/**
	 * @IDH\Document(description="Object", type="mixed")
     */
	public $object;
	
	/**
	 * @IDH\Document(description="Field", type="string")
     */
	public $field;
	
	/**
	 * @IDH\Document(description="Message", type="string")
     */
	public $message;
	
}
	