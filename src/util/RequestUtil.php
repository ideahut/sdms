<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;
use ReflectionMethod;

use \Psr\Http\Message\ServerRequestInterface as Request;

use \Awurth\SlimValidation\Validator;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\object\Result;



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
	 *   - $input bisa request atau object
	 *   - untuk request maka di validator harus menggunakan $input->getParam("name"),
	 *   - untuk object maka di validator menggunakan $input->name atau $input->getName()
	 *   
	 */
	public static function validate($controller, $method, $input = null, array $optional = null) {
		$themethod = $method;
		if (is_string($themethod) && strrpos($themethod, "::") !== false) {
			$themethod = new ReflectionMethod($themethod);
		}
		if (is_string($themethod)) {
			$namespace = "";
		    $config = $controller->getConfig();
		    if (isset($config[Common::SETTING_VALIDATOR])) {
		    	$namespace = trim($config[Common::SETTING_VALIDATOR]);
		    }
			$split = array_map("trim", explode("->", $themethod));
			if (count($split) < 2) {
		    	throw new Exception("Invalid validator format: [CLASS]->[METHOD]");
		    }
		    $nameclass = $namespace . $split[0];
		    if (!class_exists($nameclass)) {
		        throw new Exception("Validator class is not found, for: " . $nameclass);
		    }
		    $namemethod = $split[1];
		    
		    $vobject = null;
		    $vclass  = new ReflectionClass($nameclass);
		    if (!$vclass->hasMethod($namemethod)) {
		    	throw new Exception("Validator method '" . $namemethod . "' is not found in class '" . $nameclass . "'");
		    }
		    $vmethod = $vclass->getMethod($namemethod);
		    if (!$vmethod->isPublic()) {
		    	throw new Exception("Validator method '" . $namemethod . "' is not a public method in class '" . $nameclass . "'");
		    }
		    $vparams = $vmethod->getParameters();
		    $vcount  = count($vparams);
		    $vinput  = isset($input) ? $input : $controller->getRequest();
		    $vargs  = [];

			if ($vcount !== 0) {
				$vargs[0] = $vinput;
				if ($vcount > 1) {
					$vargs[1] = isset($optional) ? $optional : null;
					for ($i = 2; $i < $vcount; $i++) {
						$vargs[$i] = null;
					}
				}			
			}
			$rules = $vmethod->invoke($vmethod->isStatic() ? null : $vclass->newInstance(), $vargs);
		    if ($rules !== null) { 
		    	if ($rules instanceof Result) {
		    		return $rules;
		    	} else {
				    $validator = new Validator();
				    $result = $validator->validate($vinput, $rules);		    
				    if (!$result->isValid()) {
				    	return Result::ERROR("NOT_VALID", $validator->getErrors());
				    }
				}
			}			
		} else {
			$annotation = ObjectUtil::scanAnnotation($themethod, Common::ANNOTATION_VALIDATOR);
			if (isset($annotation[Common::ANNOTATION_VALIDATOR])) {
		    	foreach($annotation[Common::ANNOTATION_VALIDATOR] as $vstr){
		    		$result = self::validate($controller, $vstr, $input, $optional);
		    		if (isset($result)) {
		    			return $result;
		    		}
		    	}
		    }		    
		}
	    return null;
	}
	
}