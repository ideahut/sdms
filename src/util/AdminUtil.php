<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\entity\EntityDao;
use \Ideahut\sdms\object\Admin;
use \Ideahut\sdms\object\Result;

final class AdminUtil
{

	private static $ACTIONS = [
        "count", 
        "get", 
        "map", 
        "select", 
        "update", 
        "save", 
        "delete"
    ];

	/**
     * PROCESS
     * 
     * Memproses dan menvalidasi request
     */
    public static function process($app, Request $request, Response $response, $args) {
    	$settings = $app->getContainer()[Common::SETTING_SETTINGS][Common::SETTING_ADMIN];
    	$admin_resp = (new ReflectionClass(trim($settings[Common::SETTING_RESPONSE])))->newInstanceArgs();
        try {
            // PATH
            $path_array = array_map("trim", explode("/", $args["path"]));
            $path_count = count($path_array);
            if ($path_array[$path_count - 1] === "") {
                $path_array = array_slice($path_array, 0, -1);
                $path_count = count($path_array);
            }
            if ($path_count === 0) {
                return $response->withStatus(404);
            }

            // ACCESS
            if (isset($settings[Common::SETTING_ACCESS])) {
                $access_setting = $settings[Common::SETTING_ACCESS];
                $access_class   = trim($access_setting[Common::SETTING_CLASS]);
                $access_param   = null;
                if (isset($access_setting[Common::SETTING_PARAMETER])) {
                    $access_param = $access_setting[Common::SETTING_PARAMETER];
                }
                $access_object = (new ReflectionClass($access_class))->newInstanceArgs(array($access_param));
                $access_data = $access_object->validate($request, $app, $args);
                if ($access_data instanceof Result) {
                    return $admin_resp->response($request, $response, $access_data);
                }                
            }

            $action = strtolower($path_array[0]);
            if (!in_array($action, self::$ACTIONS)) {
            	$result = Result::ERROR(Result::ERR_INVALID, "action: " . $action);
                return $admin_resp->response($request, $response, $result);
            }
            $admin = RequestUtil::bodyToObject($request, Admin::class);
            if (!isset($admin->entity)) {
                $result = Result::ERROR(Result::ERR_REQUIRED, "entity");
                return $admin_resp->response($request, $response, $result);
            }
            $entity_class = self::getEntityClass($settings, trim($admin->entity));
            if (null === $entity_class) {
                $result = Result::ERROR(Result::ERR_NOT_FOUND, "entity");
                return $admin_resp->response($request, $response, $result);   
            }
            $manager    = $app->getContainer()->get(Common::SETTING_ENTITY_MANAGER);
            $manager    = $app->getContainer()->get(Common::SETTING_ENTITY_MANAGER);
            $dao_class  = new ReflectionClass(EntityDao::class);
            
            if (!$dao_class->hasMethod($action)) {
                $result = Result::ERROR(Result::ERR_NOT_SUPPORTED, "Action (" . $action . ")");
                return $admin_resp->response($request, $response, $result);
            }
            
            $dao_method = $dao_class->getMethod($action);
            $dao_obj    = $dao_class->newInstance();
            
            $dao_obj
                ->manager($manager)
                ->entity_class($entity_class)
                ->pk($admin->pk)
                ->page($admin->page)
                ->start($admin->start)
                ->limit($admin->limit)
                ->filter($admin->filter)
                ->order($admin->order)
                ->group($admin->group)
                ->field($admin->field)
                ->value($admin->value);
            if (isset($settings[Common::SETTING_DEBUG]) && $settings[Common::SETTING_DEBUG] === true) {
                $logger = $app->getContainer()->get(Common::SETTING_LOGGER);
                $dao_obj->logger($logger);
            }

            $data = null;
            if ($action === "map") {
                $data = $dao_method->invoke($dao_obj, $admin->mapkey);
            } else {
                $data = $dao_method->invoke($dao_obj);
            }
            $result = Result::SUCCESS($data);
            return $admin_resp->response($request, $response, $result);
        } catch(Exception $e) {
            $app->getContainer()->get(Common::SETTING_LOGGER)->error($e->getMessage());
            $result = Result::ERROR(Result::ERR_ANY, $e->getMessage());
            return $admin_resp->response($request, $response, $result);
        }
    }

    private static function getEntityClass($settings, $classname) {
        $namespaces = [];
        if (isset($settings[Common::SETTING_ENTITY])) {
            $namespaces = $settings[Common::SETTING_ENTITY];
            if (is_string($namespaces)) {
                $namespaces = [trim($namespaces)];
            } else if (is_array($namespaces)) {
                $namespaces = $path_array = array_map("trim", $namespaces);
            }
        }
        foreach ($namespaces as $item) {
            if (class_exists($item . $classname)) {
                return $item . $classname;
            }
        }
        if (class_exists($classname)) {
            return $classname;
        }
        return null;
    }

}