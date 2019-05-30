<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\base\BaseFormat;

final class ObjectUtil
{

	// RULE
	const NOT_NULL		= 0;
	const NOT_EMPTY		= 1;
	const IS_OBJECT		= 2;
	const IS_ARRAY		= 3;
	const IS_BOOL		= 4;
	const IS_NUMERIC	= 5;
	const IS_DOUBLE		= 6;
	const IS_FLOAT		= 7;
	const IS_LONG		= 8;
	const IS_INTEGER	= 9;


	// PROPERTY TYPE
	const PUBLIC			= "public";
	const PRIVATE			= "private";
	const PROTECTED			= "protected";
	const STATIC			= "static";

	// COPY
	const PROPERTY				= 1;
	const SET_GET				= 2;
	const PROPERTY_AND_SET_GET	= 3;

	//const SKIP_FIELDS		= array(Entity::ID, Entity::CREATED, Entity::UPDATED);

	

	/**
	 * copy
	 *   - Memindahkan nilai-nilai property atau method dari $src ke $dest
	 *   - $ignore berisi daftar property yang tidak perlu di-copy
	 *   - $rule berisi kondisi dari $src ke $dest, tediri dari: not null, not empty, dll
	 */
	public static function copy($src, $dest, $type = self::PROPERTY, array $ignore = [], array $rule = []) {
		if (!is_object($src) || !is_object($dest)) {
			return;
		}
		$cls_src  = get_class($src);
		$cls_dest = get_class($dest);
		if ($cls_src !== $cls_dest) {
			throw new Exception("Source class is not equal to destination class");
		}
		$ref_class = new ReflectionClass($cls_src);
		switch ($type) {
			case self::PROPERTY:
				self::_copy_property($ref_class, $src, $dest, $ignore, $rule);
				break;
			case self::SET_GET:
				self::_copy_set_get($ref_class, $src, $dest, $ignore, $rule);
				break;
			case self::PROPERTY_AND_SET_GET:
				self::_copy_set_get($ref_class, $src, $dest, $ignore, $rule);
				self::_copy_property($ref_class, $src, $dest, $ignore, $rule);
				break;
		}
	}
	
	private static function _copy_set_get($ref_class, $src, $dest, $ignore, $rule) {
		$methods = $ref_class->getMethods();
		foreach ($methods as $method) {
			if (substr($method->name, 0, 3) !== "set") {
				continue;
			}
			$suffix = substr($method->name, 3);
			if (!$ref_class->hasMethod("get" . $suffix)) {
				continue;
			}
			$getter = $ref_class->getMethod("get" . $suffix);
			$value 	= $getter->invoke($src);
			$name   = strtolower(substr($suffix, 0, 1)) . substr($suffix, 1);
			if (self::_can_copy($name, $value, $ignore, $rule)) {
				$method->invoke($dest, $value);
			}
		}
	}

	private static function _copy_property($ref_class, $src, $dest, $ignore, $rule) {
		$properties = $ref_class->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($properties as $property) {
			$name 	= $property->getName();
			$value 	= $property->getValue($src);
			if (self::_can_copy($name, $value, $ignore, $rule)) {
				$property->setValue($dest, $value);
			}
		}
	}

	private static function _can_copy($name, $value, array $ignore = [], array $rule = []) {
		if (in_array($name, $ignore)) {
			return false;
		}
		if (!isset($rule[$name])) {
			return true;
		}
		$check = $rule[$name];
		if (!is_array($check)) {
			$check = [$check];
		}
		$count = count($check);
		$result = true;
		for ($i = 0; $i < $count; $i++) {
			if (!$result) {
				return $result;
			}
			switch ($check[$i]) {
				case self::NOT_NULL:
					$result = $value !== null;
					break;
				case self::NOT_EMPTY:
					$result = $value !== null && $value !== "";
					break;
				case self::IS_OBJECT:
					$result = is_object($value);
					break;
				case self::IS_ARRAY:
					$result = is_array($value);
					break;
				case self::IS_BOOL:
					$result = is_bool($value);
					break;
				case self::IS_NUMERIC:
					$result = is_numeric($value);
					break;
				case self::IS_DOUBLE:
					$result = is_double($value);
					break;
				case self::IS_FLOAT:
					$result = is_float($value);
					break;
				case self::IS_LONG:
					$result = is_long($value);
					break;
				case self::IS_INTEGER:
					$result = is_integer($value);
					break;
			}
		}
		return $result;
	}
	
	
	
	
	/**
	 * Membaca Annotation di comment, seperti: @DESCRIPTION, @PARAMETER, @RETURN
	 * $names = spesifik nama yang dicari, cth: ['DESCRIPTION', 'PARAMETER']
	 */
	public static function scanAnnotation($class_or_method_or_property) {
		$result = [];
		$src = $class_or_method_or_property;		
		if (is_string($src)) {
	        $split = explode("::", $src);
	        $split[0] = trim($split[0]);
	        if (!class_exists($split[0])) {
	            return $result;
	        }
	        if (count($split) > 1) {
	            $class = new ReflectionClass($split[0]);
	            $split[1] = trim($split[1]);
	            if ($class->hasMethod($split[1])) {
	                $src = new ReflectionMethod($split[0], $split[1]);
	            } else if ($class->hasProperty($split[1])) {
	                $src = new ReflectionProperty($split[0], $split[1]);
	            } else {
	                return $result;
	            }
	        } else {
	            $src = new ReflectionClass($split[0]);
	        }
	    }
	    if (!method_exists($src, 'getDocComment')) {
	        return $result;
	    }
	    $doc = $src->getDocComment();
	    preg_match_all('#@(.*?)\n#s', $doc, $annotations);
	    $annotations = $annotations[1];
	    $argv = func_get_args();
    	$narg = func_num_args();
	    if ($narg === 1) {
	    	foreach ($annotations as $str) {
	    		array_push($result, $str);
	    	} 
	    } else {
	    	foreach ($annotations as $str) {
	    		$key = null;
	    		$val = "";
	    		for ($i = 1; $i < $narg; $i++) {
	    			if (substr($str, 0, strlen($argv[$i])) === $argv[$i]) {
	    				$key = $argv[$i];
	    				$val = substr($str, strlen($argv[$i]));
	    				break;
	    			}
	    		}
	    		if ($key === null) continue;
	    		if (!isset($result[$key])) {
	            	$result[$key] = [];
	        	}
	        	array_push($result[$key], $val);	
	    	}
	    }
	    return $result;
	}	
	
	
	
	/**
	 * Untuk mengubah string ke array, contoh: a=1&b=2 menjadi ["a"=>"1", "b"=>"2"]
	 */
	public static function parseStr($str, $start = "", $end = "") {
		if (!is_string($str)) {
			return [];
		}
		$txt = $str;
		if ($start !== "") {
			$pos = strpos($txt, $start);
			if ($pos !== false) {
				$txt = substr($txt, $pos + 1);
			}
		}
		if ($end !== "") {
			$pos = strrpos($txt, $end);
			if ($pos !== false) {
				$txt = substr($txt, 0, $pos);
			}	
		}
		parse_str($txt, $result);
		return $result;
	}


	/**
	* Untuk mengubah object menjadi array format
	*/
	public static function formatObject($object, $show_null = false) {
		$result = null;
		
		// Array
		if (is_array($object)) {
			$result = [];			
			if (array_keys($object) !== range(0, count($object) - 1)) { // associative
				foreach ($object as $key => $value) {
					if (null === $value && false === $show_null) {
						continue;
					}
					$result[$key] = self::formatObject($value);
				}
			} else {
				for ($i = 0; $i < count($object); $i++) {
					if (null === $object[$i] && false === $show_null) {
						continue;
					}
					$result[$i] = self::formatObject($object[$i]);
				}
			}
		} 

		// Object
		else if (is_object($object)) {
			if ("stdClass" === get_class($object)) {
				$array = (array)$object;
				foreach ($array as $key => $value) {
					if (null === $value && false === $show_null) {
						continue;
					}
					$result[$key] = self::formatObject($value);
				}
			} else if ($object instanceof BaseFormat) {
				$result = $object->toFormatObject();
			} else {
				$class = new ReflectionClass(\Doctrine\Common\Util\ClassUtils::getRealClass(get_class($object)));
				//$class = new ReflectionClass(get_class($object));
				$annot = self::scanAnnotation($class, Common::ANNOTATION_FORMAT);
				if (isset($annot[Common::ANNOTATION_FORMAT])) {
					$class_show_null = $show_null;
					$arr = $annot[Common::ANNOTATION_FORMAT];
					$str = count($arr) > 0 ? $arr[0] : null;
					$param = self::parseStr($str, "(", ")");
					if (isset($param["show_null"])) {
						$shnull = $param["show_null"];
						$class_show_null = "1" === $shnull || "true" === $shnull;
					}

					$result = [];
					$props = $class->getProperties(ReflectionProperty::IS_PUBLIC);
					foreach ($props as $prop) {
						$prop_show_null = $class_show_null;
						$annot = self::scanAnnotation($prop, Common::ANNOTATION_FORMAT);
						if (!isset($annot[Common::ANNOTATION_FORMAT])) {
							continue;
						}
						$key = $prop->getName();
						$str = $annot[Common::ANNOTATION_FORMAT];
						if (count($str) > 0) {
							$str = $str[0];
							$param = self::parseStr($str, "(", ")");
							if (isset($param["alias"])) {
								$key = trim($param["alias"]);
							}
							if (isset($param["show_null"])) {
								$shnull = $param["show_null"];
								$prop_show_null = "1" === $shnull || "true" === $shnull;			
							}
						}
						$value = $prop->getValue($object);
						if ($value !== null) {
							$result[$key] = self::formatObject($value);	
						} else {
							if ($prop_show_null) {
								$result[$key] = self::formatObject($value);	
							}
						}
					}
				} else if ($object instanceof \DateTime) {
					$result = $object->getTimestamp();
				} else {
					$result = $object;		
				}				
			}
		} else {
			$result = $object;
		}
		return $result;
	}	
	


	/**
	 * arrayToObject
	 *   - Untuk merubah data menjadi class object.
	 */
	public static function arrayToObject(array $array, $class, array $ignoredKeys = []) 
	{
		$ref_class = new ReflectionClass($class);
		$result = null;
		if (array_keys($array) !== range(0, count($array) - 1)) { // associative
			$result = $ref_class->newInstance();
			foreach($array as $key => $value) {
				self::setObjectValue($ref_class, $result, $key, $value, $ignoredKeys);
			}
		} else {
			$result = [];
			for ($i = 0; $i < count($array); $i++) {
				$instance = $ref_class->newInstance();
				foreach($array[$i] as $key => $value) {
					self::setObjectValue($ref_class, $instance, $key, $value, $ignoredKeys);
				}
				$result[$i] = $instance;	
			}
		}
		return $result;
	}

	
	/**
	 * setObjectValue
	 *   - Untuk mengisi nilai object.
	 */
	public static function setObjectValue($ref_class, $instance, $key, $value, array $ignoredKeys = [])  {
		if (null === $value || in_array($key, $ignoredKeys)) {
			return;
		}
		if (is_object($value)) {
			$oclass = get_class($value);
			if ("stdClass" === $oclass || "SimpleXMLElement" === $oclass) {
				$value = (array)$value;
			}
		}
		$exp = explode(Common::SPLIT_OBJECT_FIELD, $key);
		$cnt = count($exp);
		if ($cnt === 1) {
			$name = trim($exp[0]);
			if ($name === "") {
				return;
			}
			$mtd_nm = strtoupper(substr($name, 0, 1)) . substr($name, 1);
			
			if ($ref_class->hasMethod("get" . $mtd_nm) && $ref_class->hasMethod("set" . $mtd_nm)) {
				$mtd_set = $ref_class->getMethod("set" . $mtd_nm);
				$types 	 = $mtd_set->getParameters();
				if (count($types) === 1) {
					if (null !== $types[0]->getClass()) {
						if (is_object($value) && get_class($value) === $types[0]->getClass()) {
							$mtd_set->invoke($instance, $value);
						} else if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
							$new_ins = self::tryCreateInstance($types[0]->getClass()->name);
							if (null === $new_ins) {
								return;
							}
							foreach ($value as $mkey => $mval) {
								self::setObjectValue(new ReflectionClass(get_class($new_ins)), $new_ins, $mkey, $mval);			
							}
						}
					} else {
						$mtd_set->invoke($instance, $value);
					}
				}

			} else if ($ref_class->hasProperty($name)) {
				$prop = $ref_class->getProperty($name);
				if ($prop->isPublic()) {
					$annotation = self::scanAnnotation($prop, Common::ANNOTATION_FORMAT);
					if (isset($annotation[Common::ANNOTATION_FORMAT])) {
						$param = self::parseStr($annotation[Common::ANNOTATION_FORMAT][0], "(", ")");
						if (isset($param["type"])) {
							$new_ins = self::tryCreateInstance(trim($param["type"]));
							if (null === $new_ins) {
								return;
							}
							if (is_object($value) && get_class($value) === get_class($new_ins)) {
								$prop->setValue($instance, $value);
							} else if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
								foreach ($value as $mkey => $mval) {
									self::setObjectValue(new ReflectionClass(get_class($new_ins)), $new_ins, $mkey, $mval);			
								}
								$prop->setValue($instance, $new_ins);
							}
						} else {
							$prop->setValue($instance, $value);
						}
					} else {
						$prop->setValue($instance, $value);
					}					
				}
			}
		} else {
			$tmp = [[$instance, null, null]]; // format: (instance, method_set, property), index 0  diisi dulu
			for ($i = 0; $i < $cnt - 1; $i++) {
				$suffix_mtd = strtoupper(substr($exp[$i], 0, 1)) . substr($exp[$i], 1);					
				$parent_obj = $tmp[$i][0];
				$parent_cls = new ReflectionClass(get_class($parent_obj));
				
				// Method
				if ($parent_cls->hasMethod("get" . $suffix_mtd) && $parent_cls->hasMethod("set" . $suffix_mtd)) {
					$mtd_get = $parent_cls->getMethod("get" . $suffix_mtd);
					$mtd_set = $parent_cls->getMethod("set" . $suffix_mtd);
					$obj_val = $mtd_get->invoke($parent_obj);
					if (null == $obj_val) {
						// Mengambil type dari method set
						$types = $mtd_set->getParameters();
						if (count($types) === 1 && null !== $types[0]->getClass()) {
							$obj_val = self::tryCreateInstance($types[0]->getClass()->name);
						}	
					}
					if (null === $obj_val) {
						unset($tmp);
						return;
					}
					$tmp[$i][1] = $mtd_set; // update method set untuk parent
					array_push($tmp, [$obj_val, null, null]); // tambahkan value baru
				} 
				// Property
				else if ($parent_cls->hasProperty($exp[$i])) {
					$obj_val = null;
					$prop = $parent_cls->getProperty($exp[$i]);						
					if ($prop->isPublic()) {
						$obj_val = $prop->getValue($instance);
						if (null === $obj_val) {
							// Mengambil type dari annotation FORMAT
							$annotation = self::scanAnnotation($prop, Common::ANNOTATION_FORMAT);
							if (isset($annotation[Common::ANNOTATION_FORMAT])) {
								$param = self::parseStr($annotation[Common::ANNOTATION_FORMAT][0], "(", ")");
								if (isset($param["type"])) {
									$obj_val = self::tryCreateInstance(trim($param["type"]));
								}
							}	
						}	
					}						
					if (null === $obj_val) {
						unset($tmp);
						return;
					}
					$tmp[$i][2] = $prop; // update reflection property untuk parent 	
					array_push($tmp, [$obj_val, null, null]); // tambahkan value baru
				} 
				// Tidak dikenali
				else {
					unset($tmp);
					return;
				}
			}

			$count_tmp 	= count($tmp);
			$last_obj 	= $tmp[$count_tmp - 1][0];
			$last_cls 	= new ReflectionClass(get_class($last_obj));
			$last_set	= "set" . strtoupper(substr($exp[$count_tmp - 1], 0, 1)) . substr($exp[$count_tmp - 1], 1);
			if ($last_cls->hasMethod($last_set)) {
				$mtd_set = $last_cls->getMethod($last_set);
				$mtd_set->invoke($last_obj, $value);
			} else if ($last_cls->hasProperty($exp[$count_tmp - 1])) {
				$prop = $last_cls->getProperty($exp[$count_tmp - 1]);
				if (!$prop->isPublic()) {
					unset($tmp);
					return;	
				}
				$prop->setValue($last_obj, $value);
			} else {
				unset($tmp);
				return;	
			}
			for ($i = $count_tmp - 2; $i >= 0; $i--) {
				if (isset($tmp[$i][1])) {
					$tmp[$i][1]->invoke($tmp[$i][0], $tmp[$i + 1][0]);	
				} else {
					$tmp[$i][2]->setValue($tmp[$i][0], $tmp[$i + 1][0]);
				}					
			}			
			unset($tmp);
		}
		unset($exp);
	}

	private static function tryCreateInstance($class) {
		try {
			$ref_class = new ReflectionClass($class);
			$instance  = $ref_class->newInstance();
			return $instance;
		} catch (Exception $e) {
			return null;
		}
	}
}