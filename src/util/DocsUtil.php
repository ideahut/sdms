<?php
namespace Ideahut\sdms\util;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use ReflectionClass;
use ReflectionMethod;

use \Ideahut\sdms\Common;


final class DocsUtil {

	/**
     * HTML
     * 
     * Menampilkan semua endpoint di controller termasuk parameter, return, dan entity dalam format HTML
     */
    public static function html($app, Request $request, Response $response, $args) {
        $SETTINGS   = $app->getContainer()[Common::SETTING_SETTINGS];
        $DOCUMENT   = $SETTINGS[Common::SETTING_DOCUMENT];
        $ENTITY     = $DOCUMENT[Common::SETTING_ENTITY];
        $CONTROLLER = $DOCUMENT[Common::SETTING_CONTROLLER];
        
        $PREFIX_TXT = "entity::";
        $PREFIX_LEN = strlen($PREFIX_TXT);
        
        $entity_space = [];
        foreach($ENTITY as $namespace) {
            $entity_space[$namespace] = scandir(self::getDirectory($SETTINGS, $namespace));
        }
        
        $ctrl_space = [];
        foreach($CONTROLLER as $namespace) {
            $ctrl_space[$namespace] = scandir(self::getDirectory($SETTINGS, $namespace));
        }
        
        $out = $response->getBody();
        
        $out->write("<!doctype html>\n<html>\n");
        $out->write("<head>\n<style>\n");
        $out->write("body {padding: 0px;}\n");
        $out->write("ul {padding-left: 12px; margin: 0px;}\n");
        $out->write("#ctrl {font-family: \"Trebuchet MS\", Arial, Helvetica, sans-serif; margin-bottom: 20px; border-collapse: collapse; width: 100%;}\n");
        $out->write("#ctrl td, #ctrl th {border: 1px solid #ddd; padding: 8px;}\n");
        $out->write("#ctrl tr:nth-child(even){background-color: #f2f2f2;}\n");
        $out->write("#ctrl tr:hover {background-color: #ddd;}\n");
        $out->write("#ctrl th {padding: 6px 0px 6px 0px; text-align: center; background-color: #4CAF50; color: white;}\n");
        $out->write("#ctrl th.title {padding: 10px 0px 10px 0px; text-align: center; background-color: #CCAF55; color: white; font-size: 18px;}\n");
        $out->write("#entity {font-family: \"Trebuchet MS\", Arial, Helvetica, sans-serif; margin-bottom: 20px; border-collapse: collapse;width: 100%;}\n");
        $out->write("#entity td, #entity th {border: 1px solid #e3f2fd; padding: 8px;}\n");
        $out->write("#entity tr:nth-child(even){background-color: #f2f2f2;}\n");
        $out->write("#entity tr:hover {background-color: #ddd;}\n");
        $out->write("#entity th {padding: 6px 0px 6px 0px; text-align: center; background-color: #55AFCC; color: white;}\n");
        $out->write("#entity th.title {padding: 10px 0px 10px 0px; text-align: center; background-color: #FFAF55; color: white; font-size: 18px;}\n");
        $out->write("</style>\n</head>\n");
        $out->write("<body style=\"font: 14px/1.5 Helvetica,Arial,Verdana,sans-serif;\">\n");
        
        // CONTROLLER
        foreach($ctrl_space as $namespace=>$ctrl_files) {
            foreach($ctrl_files as $file) {
                $len = strlen($file);
                if (substr($file, $len - 4, $len) !== '.php') {
                    continue;
                }
                $class = new ReflectionClass($namespace . substr($file, 0, $len - 4));
                if (self::isIgnoredForDocument($class)) continue;

                $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
                if (count($methods) === 0) continue;

                $out->write("<table width=\"100%\" id=\"ctrl\">\n");
                $out->write("<thead>\n");
                $out->write("<tr><th colspan=\"4\" align=\"center\" class=\"title\"><span id=\"controller_" . $class->getShortName() . "\">" . $class->getShortName() . "</span></th></tr>\n");
                $out->write("<tr><th width=\"40%\">PATH</th><th width=\"10%\">METHOD</th><th width=\"30%\">PARAMETER</th><th width=\"20%\">RETURN</th></tr>\n");
                $out->write("</thead>\n");
                $out->write("<tbody>\n");
                
                $mpath = substr($class->getShortName(), 0, strlen($class->getShortName()) -10);
                $mpath = "/" . strtolower(substr($mpath, 0, 1)) . substr($mpath, 1);
                
                foreach($methods as $method) {

                    if (self::isIgnoredForDocument($method)) continue;

                    $path = str_replace("__", "/", $method->name);
                    $path = $mpath . "/" . $path;

                    $annotations = ObjectUtil::scanAnnotation(
                        $method,
                        Common::ANNOTATION_DESCRIPTION, 
                        Common::ANNOTATION_PARAMETER, 
                        Common::ANNOTATION_BODY, 
                        Common::ANNOTATION_RETURN,
                        Common::ANNOTATION_PUBLIC,
                        Common::ANNOTATION_METHOD
                    );
                    $description = "";
                    if (isset($annotations[Common::ANNOTATION_DESCRIPTION])) {
                        $list = $annotations[Common::ANNOTATION_DESCRIPTION];
                        for ($i = 0; $i < count($list); $i++) {
                            $description .= "<br/><small><i>" . trim($list[$i]) . "</i></small>";
                        }
                    }
                    
                    if (isset($annotations[Common::ANNOTATION_PUBLIC])) {
                        $description .= "<br/><small><i><b><font color=\"#F00\">@" . Common::ANNOTATION_PUBLIC . "</font></b></i></small>";                        
                    }

                    $httpmethods = "";
                    if (isset($annotations[Common::ANNOTATION_METHOD])) {
                        $strmtd = trim($annotations[Common::ANNOTATION_METHOD][0]);
                        if ($strmtd !== "") {
                            $arrmtd = array_map("strtoupper", array_map("trim", explode(",", $strmtd)));
                            $httpmethods = implode( ", ", $arrmtd);
                        }
                    }
                    
                    $parameter = "";
                    if (isset($annotations[Common::ANNOTATION_PARAMETER])) {
                        $parameter .= "<ul>\n";
                        $list = $annotations[Common::ANNOTATION_PARAMETER];
                        foreach ($list as $value) {
                            $value = trim($value);
                            $parameter .= "<li>";
                            $split = explode("=>", $value);
                            $name = explode("->", $split[0]);
                            $endstr = "";
                            $parameter .= "<b>";
                            for ($i = 0; $i < count($name); $i++) {
                                $name[$i] = trim($name[$i]);
                                if ($i != 0) {
                                    $parameter .= "&lt;";
                                    $endstr .= "&gt;";
                                }
                                if ($PREFIX_TXT === substr(strtolower($name[$i]), 0, $PREFIX_LEN)) {
                                    $parameter .= "<a href=\"#entity_".substr($name[$i], $PREFIX_LEN)."\">".substr($name[$i], $PREFIX_LEN)."</a>";
                                } else {
                                    $parameter .= $name[$i];
                                }
                            }
                            $parameter .= $endstr . "</b>";
                            if (count($split) > 1) {
                                $parameter .= "<br/><small><i>".$split[1]."</i></small>";
                            }
                            $parameter .= "</li>\n";
                        }
                        $parameter .= "</ul>\n";
                    }
                    
                    if (isset($annotations[Common::ANNOTATION_BODY])) {
                        if (isset($annotations[Common::ANNOTATION_PARAMETER])) {
                            $parameter .= "<br/>";
                        }
                        $parameter .= "<small><i>Body:</i></small> ";
                        $list = $annotations[Common::ANNOTATION_BODY];
                        foreach($list as $value) {
                            $name = explode("->", $value);
                            $endstr = "";
                            $parameter .= "<b>";
                            for ($i = 0; $i < count($name); $i++) {
                                $name[$i] = trim($name[$i]);
                                if ($i != 0) {
                                    $parameter .= "&lt;";
                                    $endstr .= "&gt;";
                                }
                                if ($PREFIX_TXT === substr(strtolower($name[$i]), 0, $PREFIX_LEN)) {
                                    $parameter .= "<a href=\"#entity_".substr($name[$i], $PREFIX_LEN)."\">".substr($name[$i], $PREFIX_LEN)."</a>";
                                } else {
                                    $parameter .= $name[$i];
                                }
                            }
                            $parameter .= $endstr."</b>";
                        }
                    }                    
                    $parameter .= "&nbsp;";
                    
                    $return = "";
                    if (isset($annotations[Common::ANNOTATION_RETURN])) {
                        $list = $annotations[Common::ANNOTATION_RETURN];
                        foreach($list as $value) {
                            $value = trim($value);
                            $name = explode("->", $value);
                            $endstr = "";
                            $return .= "<b>";
                            for ($i = 0; $i < count($name); $i++) {
                                $name[$i] = trim($name[$i]);
                                if ($i != 0) {
                                    $return .= "&lt;";
                                    $endstr .= "&gt;";
                                }
                                if ($PREFIX_TXT === substr(strtolower($name[$i]), 0, $PREFIX_LEN)) {
                                    $return .= "<a href=\"#entity_".substr($name[$i], $PREFIX_LEN)."\">".substr($name[$i], $PREFIX_LEN)."</a>";
                                } else {
                                    $return .= $name[$i];
                                }
                            }
                            $return .= $endstr."</b><br/>";
                        }
                    }
                    $return = $return."&nbsp;";
                    
                    $out->write("<tr><td valign=\"top\"><b>". $path ."</b>". $description ."</td><td valign=\"top\">". $httpmethods ."</td><td valign=\"top\">". $parameter ."</td><td valign=\"top\">" . $return ."</td></tr>\n");
                    
                }
                
                $out->write("</tbody>\n");
                $out->write("</table>\n");
            }
        }
        
        
        // ENTITY
        foreach($entity_space as $namespace=>$entity_files) {
            foreach($entity_files as $file) {
                $len = strlen($file);
                if (substr($file, $len - 4, $len) !== '.php') {
                    continue;
                }
                
                $class = new ReflectionClass($namespace . substr($file, 0, $len - 4));
                if (self::isIgnoredForDocument($class)) continue;

                $out->write("<table width=\"100%\" id=\"entity\">\n");
                $out->write("<thead>\n");
                $out->write("<tr><th colspan=\"3\" class=\"title\"><center><b><span id=\"entity_" . $class->getShortName() . "\"><b>" . $class->getShortName() . "</b></span></center></th></tr>\n");
                $out->write("<tr><th align=\"center\" width=\"34%\"><b>FIELD</b></th><th align=\"center\" width=\"33%\"><b>TYPE</b></th><th align=\"center\" width=\"33%\"><b>DESCRIPTION</b></th></tr>\n");
                $out->write("</thead>\n");
                $out->write("<tbody>\n");
                foreach ($class->getProperties() as $prop) {
                    if (self::isIgnoredForDocument($prop)) continue;
                    $annotations = ObjectUtil::scanAnnotation(
                        $prop, 
                        'ORM\\Column',
                        'ORM\\ManyToOne',
                        'ORM\\OneToMany',
                        Common::ANNOTATION_DESCRIPTION,
                        Common::ANNOTATION_TYPE,
                        Common::ANNOTATION_FORMAT
                    );
                    $type = "";
                    if (isset($annotations['ORM\\Column'])) {
                        $column = trim($annotations['ORM\\Column'][0]);
                        $pos = strpos($column, '(');
                        if ($pos !== false) {
                            $column = trim(substr($column, $pos + 1));
                        }
                        $pos = strrpos($column, ')');
                        if ($pos !== false) {
                            $column = trim(substr($column, 0, $pos));
                        }
                        $exp = explode(",", $column);
                        foreach ($exp as $ss) {
                            $ss = explode("=", $ss);
                            if (strtolower(trim($ss[0])) === "type") {
                                $type = trim($ss[1]);
                                $type = substr($type, 1, -1);
                                break;
                            }
                        }
                    } 
                    
                    if (isset($annotations['ORM\\ManyToOne']) || isset($annotations['ORM\\OneToMany'])) {
                        $is_OneToMany = isset($annotations['ORM\\OneToMany']);
                        $column = trim($annotations[$is_OneToMany ? 'ORM\\OneToMany' : 'ORM\\ManyToOne'][0]);
                        $pos = strpos($column, '(');
                        if ($pos !== false) {
                            $column = trim(substr($column, $pos + 1));
                        }
                        $pos = strrpos($column, ')');
                        if ($pos !== false) {
                            $column = trim(substr($column, 0, $pos));
                        }
                        $exp = explode(",", $column);
                        foreach ($exp as $ss) {
                            $ss = explode("=", $ss);
                            if (strtolower(trim($ss[0])) === "targetentity") {
                                $type = substr(trim($ss[1]), 1, -1);
                                $type = "<a href=\"#entity_" . $type . "\">" . $type . "</a>";
                                break;
                            }
                        }
                    }
                    
                    if (isset($annotations[Common::ANNOTATION_TYPE])) {
                        $type = trim($annotations[Common::ANNOTATION_TYPE][0]);
                    }
                                        
                    $description = "";
                    if (isset($annotations[Common::ANNOTATION_DESCRIPTION])) {
                        $list = $annotations[Common::ANNOTATION_DESCRIPTION];
                        for ($i = 0; $i < count($list); $i++) {
                            if ($i != 0) {
                                $description .= "<br/>";
                            }
                            $description .= trim($list[$i]);
                        }
                    }

                    $name = $prop->name;
                    if (isset($annotations[Common::ANNOTATION_FORMAT])) {
                        $str = $annotations[Common::ANNOTATION_FORMAT][0];
                        $param = ObjectUtil::parseStr($str, "(", ")");
                        if (isset($param["alias"])) {
                            $name = trim($param["alias"]);
                        }
                    }
                    
                    $out->write("<tr><td valign=\"top\"><b>". $name ."</b></td><td valign=\"top\">". $type ."&nbsp;</td><td valign=\"top\">" . $description ."&nbsp;</td></tr>\n");
                }
                $out->write("</tbody>\n");
                $out->write("</table><br/>\n");
            }
        }
        
        $out->write("</body>\n</html>");
        
        return $response;
        
    }
    
    private static function getDirectory($settings, $namespace) {
        $pos = strpos($namespace, '\\Ideahut\\sdms\\');
        if ($pos === 0) {
            return Common::SELF_DIR . '/src/';
        }

        $pos = strpos($namespace, 'Ideahut\\sdms\\');
        if ($pos === 0) {
            return Common::SELF_DIR . '/src/';   
        }

        $APP_DIR = $settings[Common::SETTING_APP_DIR];
        foreach($settings[Common::SETTING_NAMESPACE_DIR] as $prefix => $location) {
            $pos = strpos($namespace, $prefix);
            if ($pos === 0) {
                $location = '/' . str_replace('\\', '/', $location);
                return $APP_DIR . $location;
            }
        }
        throw new Exception("Invalid settings for: " . Common::SETTING_NAMESPACE_DIR);
    }

    private static function isIgnoredForDocument($class_or_method_or_property) {
        $annot = ObjectUtil::scanAnnotation($class_or_method_or_property, Common::ANNOTATION_DOCUMENT);
        if (isset($annot[Common::ANNOTATION_DOCUMENT])) {
            $arr = $annot[Common::ANNOTATION_DOCUMENT];
            if (count($arr) > 0) {
                $param = ObjectUtil::parseStr($arr[0], "(", ")");
                if (isset($param["ignore"])) {
                    $ignore = strtolower(trim($param["ignore"]));
                    if ("true" === $ignore || "1" === $ignore) {
                        return true;
                    }
                }       
            }
        }
        return false;
    }

}