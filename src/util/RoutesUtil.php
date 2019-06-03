<?php
namespace Ideahut\sdms\util;

use Exception;
use ReflectionClass;
use ReflectionMethod;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Ideahut\sdms\object\Result;
use \Ideahut\sdms\Common;

use \Ideahut\sdms\annotation\Method;
use \Ideahut\sdms\annotation\Access;

use \Ideahut\sdms\exception\ResultException;


final class RoutesUtil {

    /**
     * PROCESS
     * 
     * Memproses dan menvalidasi request
     */
    public static function process($app, $config, Request $request, Response $response, $args) {
        $route_resp = (new ReflectionClass(trim($config[Common::SETTING_RESPONSE])))->newInstanceArgs();
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
            $ctrl_name  = $path_array[0];
            $ctrl_name  = strtoupper(substr($ctrl_name, 0, 1)) . substr($ctrl_name, 1);    
            $mtd_name   = implode("__", array_slice($path_array, 1));
            unset($path_array);

            // CONTROLLER
            $suffix = "";
            if (isset($config[Common::SETTING_SUFFIX])) {
                if (isset($config[Common::SETTING_SUFFIX][Common::SETTING_CONTROLLER])) {
                   $suffix = $config[Common::SETTING_SUFFIX][Common::SETTING_CONTROLLER];
                }
            }
            $ctrl_name = $config[Common::SETTING_CONTROLLER] . $ctrl_name . $suffix;
            if (!class_exists($ctrl_name)) {
                return $response->withStatus(404);
            }
            $ctrl_class = new ReflectionClass($ctrl_name);
            if (!$ctrl_class->hasMethod($mtd_name)) {
                return $response->withStatus(404);
            }
            $ctrl_method = $ctrl_class->getMethod($mtd_name);

            // METHOD
            $annotation = ObjectUtil::scanAnnotation($ctrl_method, Method::class, Access::class);
            $request_method = isset($annotation[Method::class]) ? $annotation[Method::class][0]->value : ["GET"];
            if (is_string($request_method)) {
                $request_method = [$request_method];
            }
            $request_method = array_map("strtoupper", array_map("trim", $request_method));            
            if (!in_array($request->getMethod(), $request_method)) {
                return $response->withStatus(405);
            }

            // INSTANCE
            $ctrl_object = $ctrl_class->newInstanceArgs();
            $ctrl_object->setApp($app)->setConfig($config)->setArgs($args)->setRequest($request);

            // ACCESS
            $access_object = null;
            if (isset($config[Common::SETTING_ACCESS])) {
                $access_setting = $config[Common::SETTING_ACCESS];
                $access_class   = trim($access_setting[Common::SETTING_CLASS]);
                $access_param   = null;
                if (isset($access_setting[Common::SETTING_PARAMETER])) {
                    $access_param = $access_setting[Common::SETTING_PARAMETER];
                }
                $access_object = (new ReflectionClass($access_class))->newInstanceArgs(array($ctrl_object, $access_param));
                $access_rule = isset($annotation[Access::class]) ? $annotation[Access::class][0] : null;
                $access_data = $access_object->validate($access_rule, $ctrl_method);
                if ($access_data instanceof Result) {
                    throw new ResultException($access_data);
                }                
            }            
            $ctrl_object->setAccess($access_object);
            
            // RESULT
            $result = $ctrl_method->invoke($ctrl_object); // invoke controller method
            if (isset($result)) {
                return $route_resp->response($request, $response, $result);
            }
            return $response;
        } catch(ResultException $e) {
            return $route_resp->response($request, $response, $e->getResult());
        } catch(Exception $e) {
            $app->getContainer()->get(Common::SETTING_LOGGER)->error($e->getMessage());
            $result = Result::ERROR(Result::ERR_SYSTEM);
            return $route_resp->response($request, $response, $result);
        }
    }


    /**
     * INFO
     * 
     * Menampilkan informasi request
     */
    public static function info(Request $request, Response $response, $app, $args) {
        $out = $response->getBody();
        
        /*
        $out->write("<<< APCu Enabled >>>\n");
        $apc = extension_loaded('apcu');
        $out->write($apc);
        $out->write("\n\n");
        */

        $out->write("<<< request->getMethod() >>>\n");
        $str = $request->getMethod();
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getRequestTarget() >>>\n");
        $str = $request->getRequestTarget();
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getUri() >>>\n");
        $str = $request->getUri();
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getHeaders() >>>\n");
        $str = json_encode($request->getHeaders());
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getAttributes() >>>\n");
        $str = json_encode($request->getAttributes());
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getQueryParams() >>>\n");
        $str = json_encode($request->getQueryParams());
        $out->write($str);
        $out->write("\n\n");
        
        $out->write("<<< request->getParams() >>>\n");
        $params = $request->getParams();
        foreach($params as $key => $value) {
            $out->write("$key = $value \n");
        }
        $out->write("\n\n");
        
        $out->write("<<< request->getServerParams() >>>\n");
        $str = json_encode($request->getServerParams());
        $out->write($str);
        $out->write("\n\n");
        
        /*
        $out->write("<<< FUNCTION (request) >>>\n");
        $mtds = get_class_methods($request);
        foreach($mtds as $key) {
            $out->write("$key\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (response) >>>\n");
        $mtds = get_class_methods($response);
        foreach($mtds as $key) {
            $out->write("$key\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (application) >>>\n");
        $mtds = get_class_methods($app);
        foreach($mtds as $key) {
            $r = new ReflectionMethod("Slim\App", $key);
            $params = $r->getParameters();
            $out->write("$key = " . json_encode($params) . "\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (container) >>>\n");
        $mtds = get_class_methods($app->getContainer());
        foreach($mtds as $key) {
            $r = new ReflectionMethod("Slim\Container", $key);
            $params = $r->getParameters();
            $out->write("$key = " . json_encode($params) . "\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (logger) >>>\n");
        $mtds = get_class_methods($app->getContainer()->get('logger'));
        foreach($mtds as $key) {
            $r = new ReflectionMethod("Monolog\Logger", $key);
            $params = $r->getParameters();
            $out->write("$key = " . json_encode($params) . "\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (EntityManager) >>>\n");
        $em = $app->getContainer('settings')['entityManager'];
        $mtds = get_class_methods($em);
        foreach($mtds as $key) {
            $r = new ReflectionMethod("Doctrine\ORM\EntityManager", $key);
            $params = $r->getParameters();
            $out->write("$key = " . json_encode($params) . "\n");
        }
        $out->write("\n\n");
        
        $out->write("<<< FUNCTION (Logger) >>>\n");
        $logger = $app->getContainer('settings')['logger'];
        $mtds = get_class_methods($logger);
        foreach($mtds as $key) {
            $r = new ReflectionMethod("Monolog\Logger", $key);
            $params = $r->getParameters();
            $out->write("$key = " . json_encode($params) . "\n");
        }
        $out->write("\n\n");
        */
        return $response;
    }   
            	
}