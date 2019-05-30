<?php
namespace Ideahut\sdms\object;

/**
 * @FORMAT(show_null=false)
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
     * @DESCRIPTION Kode
     * @TYPE string atau integer
	 * @FORMAT
     */
	public $code;
	
	/**
	 * @DESCRIPTION Object
	 * @TYPE object
	 * @FORMAT
	 */
	public $object;
	
	/**
	 * @DESCRIPTION Field
	 * @TYPE string / integer
	 * @FORMAT
	 */
	public $field;
	
	/**
	 * @DESCRIPTION Deskripsi
	 * @TYPE string
	 * @FORMAT
	 */
	public $message;
	
}
	