<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;
use ReflectionMethod;

use \Psr\Http\Message\ServerRequestInterface as Request;

use \Awurth\SlimValidation\Validator;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\object\Result;

use \Ideahut\sdms\annotation\Validator as IDH_V;

use \Ideahut\sdms\exception\ResultException;


final class RequestUtil
{

	/**
	 * getUserAgent
	 *   - Untuk mendapatkan User Agent.	 
	 */
	public static function getUserAgent(Request $request) {
		$result = $request->getHeaderLine(Common::HEADER_USER_AGENT_SLIM);
		if (isset($result)) {
			return $result;
		}
		$result = $request->getHeaderLine(Common::HEADER_USER_AGENT);
		if (isset($result)) {
			return $result;
		}
		$result = $request->getHeaderLine(strtolower(Common::HEADER_USER_AGENT));
		return $result;
	}


	/**
	 * getRemoteAddr
	 *   - Untuk mendapatkan IP remote.	 
	 */
	public static function getRemoteAddr(Request $request) {
		return $request->getServerParam('REMOTE_ADDR');
	}


	/**
	 * bodyToObject
	 *   - Untuk merubah request body menjadi Object atau Array Object.	 
	 */
	public static function bodyToObject(Request $request, $class) {
		if ($request->getBody()->getSize() === 0) {
			return (new ReflectionClass($class))->newInstance();
		}
		$result = null;
		$type = $request->getContentType();
		if (strpos($type, 'text/xml') !== false || strpos($type, 'application/xml') !== false) {
			$array  = (array)simplexml_load_string($request->getBody()->getContents());
			$result = ObjectUtil::arrayToObject($array, $class);
		} else {
			$array  = (array)json_decode($request->getBody());
			$result = ObjectUtil::arrayToObject($array, $class);
		}
		return $result;
	}


	/**
	 * paramsToObject
	 *   - Untuk merubah request parameter menjadi Object.
	 */
	public static function paramsToObject(Request $request, $class, array $ignoredKeys = []) {
		$refclass = new ReflectionClass($class);
		$instance = $refclass->newInstance();
		$params   = $request->getParams();
		foreach($params as $key => $value) {
			ObjectUtil::setObjectValue($refclass, $instance, $key, $value, $ignoredKeys);
		}
		return $instance;
	}


	/**
	 * validate
	 *   - Memanggil class validator untuk menvalidasi request atau data/object.
	 *   - $method adalah ReflectionMethod atau nama method yang akan divalidasi
	 * urutan arguments:
	 *   0. service atau controller
	 *   1. method (optional) -> cth: validate($this, __METHOD__)
	 *   2. jika method tidak tersedia maka urutan selanjutnya adalah $input dan $optional 
	 *   Contoh penggunaan:
	 *		validate($this)
	 * 		validate($this, $input, $optional)
	 *		validate($this, __METHOD__)
	 * 		validate($this, __METHOD__, $input, $optional)	 
	 */
	public static function validate($service_or_controller) {
		$narg = func_num_args();
		$argv = func_get_args();
		
		$request = $service_or_controller->getRequest();

		$method = null;
		$optional = null;
		$input = null;
		$sargs = 1;
		if ($narg > $sargs) {
			if (is_string($argv[$sargs])) {
				$pos = strrpos($argv[$sargs], "::");
				if ($pos !== false && get_class($service_or_controller) === substr($argv[$sargs], 0, $pos)) {
					$method = new ReflectionMethod($argv[$sargs]);
					$sargs += 1;
				}
			}	
		}
		if (!isset($method)) {
			$ex = new Exception(); 
    		$trace = $ex->getTrace(); 
      		$caller = $trace[1];
      		if ($caller["class"] === get_class($service_or_controller)) {
      			$method = new ReflectionMethod($caller["class"] . "::" . $caller["function"]);      			
      		}      		
		}
		if ($narg > $sargs) {
			$input = $vargs[$sargs];
			$sargs += 1;
		}
		if ($narg > $sargs) {
			$optional = $vargs[$sargs];
		}

		$annotation = ObjectUtil::scanAnnotation($method, IDH_V::class);
		if (!isset($annotation[IDH_V::class])) {
			return;
		}
		$validator = $annotation[IDH_V::class][0]->value;
		if (!isset($validator)) {
			return;
		}
		if (!is_array($validator)) {
			$validator = [$validator];
		}
		foreach ($validator as $class_method) {
			if (!class_exists($class_method->class)) {
				throw new Exception("Validator class is not found, for: " . $class_method->class);
			}
			$class  = new ReflectionClass($class_method->class);
		    if (!$class->hasMethod($class_method->method)) {
		    	throw new Exception("Validator method '" . $class_method->method . "' is not found in class '" . $class_method->class . "'");
		    }
		    $method = $class->getMethod($class_method->method);
		    $result = $method->invoke($method->isStatic() ? null : $class->newInstance(), $request, $input, $optional);
		    if ($result !== null) { 
		    	if ($result instanceof Result) {
		    		throw new ResultException($result);
		    	} else {
				    $validator = new Validator();
				    $result = $validator->validate(isset($input) ? $input : $request, $result);		    
				    if (!$result->isValid()) {
				    	throw new ResultException(Result::ERROR("NOT_VALID", $validator->getErrors()));
				    }
				}
			}
		}
	}


	/**
	 * throw
	 *
	 * Send error Result as Http Response
	 *
	 */
	public static function throw(Result $result) {
		throw new ResultException($result);		
	}
		
}