<?php
namespace Ideahut\sdms\object;

use \Ideahut\sdms\annotation as IDH;

/**
 * @IDH\Format
 */
class Result
{
	const SUCCESS 		          = "SUCCESS";
	const INPROGRESS 	          = "INPROGRESS";
	const FAILED 		          = "FAILED";
	const ERROR			          = "ERROR";	

	const ERR_ANY 			      = ["00", "{0}"];
	const ERR_REQUIRED 			  = ["01", "{0} is required"];
	const ERR_NOT_FOUND 		  = ["02", "{0} is not found"];
	const ERR_EXIST 		      = ["03", "{0} is exist"];
	const ERR_NOT_SUPPORTED	      = ["04", "{0} is not supported"];
	const ERR_NOT_ALLOWED_TO      = ["05", "{0} is not allowed to {1}"];
	const ERR_INVALID             = ["06", "Invalid {0}"];
	const ERR_EXPIRED             = ["07", "{0} has expired"];
	const ERR_NOT_ACTIVE          = ["08", "{0} is not active"];
	const ERR_CANCELLED           = ["09", "{0} has been cancelled"];
	const ERR_NOT_CONFIRM         = ["10", "{0} is not confirm yet"];
	const ERR_NOT_MATCH           = ["11", "{0} did not match"];
	const ERR_NOT_ALLOWED         = ["12", "{0} is not allowed"];
	const ERR_INSUFFICIENT        = ["13", "Insufficient {0}"];
	const ERR_NOT_INSTANCEOF      = ["14", "{0} is not instanceof {1}"];
	
	const ERR_SYSTEM 			  = ["99", "System error"];	


	/**
	 * @IDH\Document(description="SUCCESS / INPROGRESS / FAILED / ERROR", type="string")
     */
	public $status;
	
	/**
	 * @IDH\Document(description="List of error", type={"Array", CodeMsg::class})
     */
	public $error; // List of CodeMsg
	
	/**
	 * @IDH\Document(description="Data", type="Array[object] / object")
     */
	public $data;
	
	/**
	 * @IDH\Document(description="Information", type="Array[{key: value}]")
     */
	public $info;	
	
	
	/*
	 * STATIC
	 */	 
	public static function STATUS($status, $data = null, $error = null) {
		$o = new self();
		$o->status 	= $status;
		$o->data 	= $data;
		$o->error 	= $error;
		return $o;
	}

	public static function SUCCESS() {
		return self::STATUS(self::SUCCESS, func_num_args() > 0 ? func_get_args()[0] : null, null);
	}
	
	public static function INPROGRESS() {
		return self::STATUS(self::INPROGRESS, func_num_args() > 0 ? func_get_args()[0] : null, null);
	}
	
	public static function FAILED() {
		return self::STATUS(self::FAILED, func_num_args() > 0 ? func_get_args()[0] : null, null);
	}
	
	public static function ERROR() {
		$argv = func_get_args();
    	$narg = func_num_args();
    	if ($narg > 0) {
    		if (is_array($argv[0])) {
    			$count = count($argv[0]);
    			if ($count > 0) {
    				if ($argv[0][0] instanceof CodeMsg) {
    					return self::STATUS(self::ERROR, null, $argv[0]);		
    				} else {
    					$msg = new CodeMsg();
						$msg->code = $argv[0][0];
						if ($count > 1) {
							$imsg = $argv[0][1] . "";
							for ($i = 1; $i < $narg; $i++) {
								$imsg = str_replace("{" . ($i - 1) . "}", $argv[$i], $imsg);
							}
							$msg->message = $imsg;
						}
						return self::STATUS(self::ERROR, null, array($msg));
    				}
    			}
    		} else {
    			$msg = new CodeMsg();
				$msg->code = $argv[0];
				if ($narg > 1) {
					if (is_array($argv[1])) {
						$msg->message = $argv[1];  // Awurth SlimValidation
					} else {
						$imsg = $argv[1] . "";
						for ($i = 2; $i < $narg; $i++) {
							$imsg = str_replace("{" . ($i - 2) . "}", $argv[$i], $imsg);
						}
						$msg->message = $imsg;
					}
				}
    			return self::STATUS(self::ERROR, null, array($msg));
    		}
    	}
    	return self::STATUS(self::ERROR, null, null);
	}	
}
