<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;
use ReflectionMethod;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\object\Result;
use \Ideahut\sdms\Common;

final class HessianUtil {

	public static function process($app, $config, Request $request, Response $response, $args) {
		$path = "/" . $args["path"];
		if (!isset($config[Common::SETTING_PATH])) {
			return $response->withStatus(500)->write("Setting " . Common::SETTING_PATH . " is not defined");
		}
		if (!isset($config[Common::SETTING_PATH][$path])) {
			return $response->withStatus(404)->write("Path is not found, for: " . $path);
		}
		$setting   = $config[Common::SETTING_PATH][$path];
		$is_public = isset($setting[Common::SETTING_IS_PUBLIC]) ? $setting[Common::SETTING_IS_PUBLIC] : false;
		if (!isset($setting[Common::SETTING_SERVICE])) {
			return $response->withStatus(500)->write("Service is not defined");
		}

		// SERVICE
		$service_name = $setting[Common::SETTING_SERVICE];
		if (!class_exists($service_name)) {
			return $response->withStatus(500)->write("Service is not found, for: " . $service_name);
		}
        $service_class  = new ReflectionClass($service_name);
        $service_object = $service_class->newInstance();
        $service_object->setApp($app)->setRequest($request);

        // ACCESS
        $access_object = null;
        if (isset($config[Common::SETTING_ACCESS])) {
            $access_setting = $config[Common::SETTING_ACCESS];
            $access_class   = trim($access_setting[Common::SETTING_CLASS]);
            $access_param   = null;
            if (isset($access_setting[Common::SETTING_PARAMETER])) {
                $access_param = $access_setting[Common::SETTING_PARAMETER];
            }
            $access_object = (new ReflectionClass($access_class))->newInstanceArgs(array($service_object, $access_param));
            $access_data = $access_object->validate($is_public);
            if ($access_data instanceof Result) {
            	$error = $access_data->error;
            	if (isset($error) && count($error) > 0) {
            		return $response->withStatus(401)->write($error[0]->code . " - " . $error[0]->message); 
            	} else {
            		return $response->withStatus(401);
            	}
            }
        }
        $service_object->setAccess($access_object);
		$server  = new \HessianService($service_object, array('displayInfo' => true, "ignoreOutput" => true, "version" => 2));
		$server->handle();
		return $response;
	}

}