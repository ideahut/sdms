<?php
namespace Ideahut\sdms\util;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use ReflectionClass;
use ReflectionMethod;

use \Doctrine\ORM\Mapping as ORM;

use \Ideahut\sdms\Common;
use \Ideahut\sdms\annotation as IDH;


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
        foreach($ctrl_space as $namespace => $ctrl_files) {
            foreach($ctrl_files as $file) {
                $len = strlen($file);
                if (substr($file, $len - 4, $len) !== '.php') {
                    continue;
                }
                $class = new ReflectionClass($namespace . substr($file, 0, $len - 4));
                $annotclass = ObjectUtil::scanAnnotation($class, IDH\Document::class);
                if (isset($annotclass[IDH\Document::class]) && $annotclass[IDH\Document::class][0]->ignore === true) {
                    continue;
                }

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
                    $annotations = ObjectUtil::scanAnnotation(
                        $method,
                        IDH\Document::class,
                        IDH\Method::class,
                        IDH\Access::class
                    );
                    
                    if (self::isIgnoredForDocument($annotations)) continue;

                    $path = str_replace("__", "/", $method->name);
                    $path = $mpath . "/" . $path;

                    $document = $annotations[IDH\Document::class][0];
                    $annotdesc = $document->description;

                    $description = "";
                    if (isset($annotdesc)) {
                        if (!is_array($annotdesc)) {
                            $annotdesc = [$annotdesc];
                        }
                        foreach ($annotdesc as $text) {
                            $description .= "<br/><small><i>" . trim($text) . "</i></small>";
                        }
                    }
                    if (isset($annotations[IDH\Access::class]) && $annotations[IDH\Access::class][0]->public === true) {
                        $description .= "<br/><small><i><b><font color=\"#F00\">PUBLIC</font></b></i></small>";
                    }

                    $httpmethods = "GET";
                    if (isset($annotations[IDH\Method::class])) {
                        $valuemethod = $annotations[IDH\Method::class][0]->value;
                        if (is_string($valuemethod)) {
                            $valuemethod = [$valuemethod];
                        }
                        $valuemethod = array_map("strtoupper", array_map("trim", $valuemethod));
                        $httpmethods = implode( ", ", $valuemethod);
                    }
                    
                    $parameter = "";
                    if (isset($document->parameter)) {
                        $docparams = $document->parameter;
                        if (!is_array($docparams)) {
                            $docparams = [$docparams];
                        }
                        $parameter .= "<ul>\n";
                        foreach ($docparams as $dparam) {
                            if (!($dparam instanceof IDH\Parameter)) {
                                continue;
                            }
                            $name = isset($dparam->name) ? trim($dparam->name) : "";
                            $parameter .= "<li>";
                            if ("" !== $name) {
                                $parameter .= "<b>" . $name . "</b>";
                            }
                            $type = self::getTypeTag($dparam->type);
                            if ($type !== "") {
                                $parameter .= ("" !== $name ? "<br/>" : "") . "<small>Type: " . $type . "</small>";
                            }
                            if (isset($dparam->description)) {
                                $parameter .= "<br/><small><i>" . $dparam->description . "</i></small>";
                            }
                            $parameter .= "</li>\n";                         
                        }
                        $parameter .= "</ul>\n";
                    }
                    
                    if (isset($document->body)) {
                        $body = self::getTypeTag($document->body);
                        if ("" !== $body) {
                            $parameter .= "<small>Body: " . $body . "</small>";
                        }
                    }
                    $parameter .= "&nbsp;";
                    
                    $return = "";
                    if (isset($document->result)) {
                        $return .= self::getTypeTag($document->result);
                    }
                    $return .= "&nbsp;";
                    $out->write("<tr><td valign=\"top\"><b>". $path ."</b>". $description ."</td><td valign=\"top\">". $httpmethods ."</td><td valign=\"top\">". $parameter ."</td><td valign=\"top\">" . $return ."</td></tr>\n");                    

                }
                    
                $out->write("</tbody>\n");
                $out->write("</table>\n");
            }
        }
        
        
        // ENTITY
        foreach($entity_space as $namespace => $entity_files) {
            foreach($entity_files as $file) {
                $len = strlen($file);
                if (substr($file, $len - 4, $len) !== '.php') {
                    continue;
                }
                
                $class = new ReflectionClass($namespace . substr($file, 0, $len - 4));
                $annotclass = ObjectUtil::scanAnnotation($class, IDH\Document::class);
                if (isset($annotclass[IDH\Document::class]) && $annotclass[IDH\Document::class][0]->ignore === true) {
                    continue;                    
                }

                $out->write("<table width=\"100%\" id=\"entity\">\n");
                $out->write("<thead>\n");
                $out->write("<tr><th colspan=\"3\" class=\"title\"><center><b><span id=\"object_" . str_replace("\\", "_", $class->name) . "\"><b>" . $class->getShortName() . "</b></span></center></th></tr>\n");
                $out->write("<tr><th align=\"center\" width=\"34%\"><b>FIELD</b></th><th align=\"center\" width=\"33%\"><b>TYPE</b></th><th align=\"center\" width=\"33%\"><b>DESCRIPTION</b></th></tr>\n");
                $out->write("</thead>\n");
                $out->write("<tbody>\n");
                foreach ($class->getProperties() as $prop) {
                    
                    $annotations = ObjectUtil::scanAnnotation(
                        $prop, 
                        ORM\Column::class,
                        IDH\Document::class
                    );
                    if (self::isIgnoredForDocument($annotations)) continue;
                    
                    $document = $annotations[IDH\Document::class][0];

                    $type = "";

                    if (isset($annotations[ORM\Column::class])) {
                        $column = $annotations[ORM\Column::class][0];
                        $type = $column->type;                         
                    } else {
                        $type = self::getTypeTag($document->type);
                    }

                    $description = "";
                    $annotdesc = $document->description;
                    if (isset($annotdesc)) {
                        if (!is_array($annotdesc)) {
                            $annotdesc = [$annotdesc];
                        }
                        foreach ($annotdesc as $text) {
                            $description .= trim($text) . "<br/>";
                        }
                        $description = substr($description, 0, -5);                        
                    }

                    $name = $prop->name;
                    if (isset($annotations[ORM\Format::class])) {
                        $format = $annotations[ORM\Format::class][0];
                        if (isset($format->alias)) {
                            $name = trim($format->alias);
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
        $libprefix = ['\\Ideahut\\sdms\\', 'Ideahut\\sdms\\'];
        foreach ($libprefix as $prefix) {
            $pos = strpos($namespace, $prefix);
            if ($pos === 0) {
                $dir = str_replace('\\', '/', substr($namespace, $pos + strlen($prefix)));
                if (substr($dir, 0, 1) === '/') {
                    $dir = substr($dir, 1);
                }
                return Common::SELF_DIR . '/' . $dir;
            }
        }
        $APP_DIR = $settings[Common::SETTING_APP_DIR];
        foreach($settings[Common::SETTING_NAMESPACE_DIR] as $prefix => $location) {
            $pos = strpos($namespace, $prefix);
            if ($pos === 0) {
                $dir = str_replace('\\', '/', substr($namespace, strlen($prefix)));
                if (substr($dir, 0, 1) === '/') {
                    $dir = substr($dir, 1);
                }
                if (substr($location, 0, 1) !== '/') {
                    $location = '/' . $location;
                }
                if (substr($location, -1) !== '/') {
                    $location = $location . '/';
                }
                return $APP_DIR . $location . $dir;
            }
        }
        throw new Exception("Invalid settings for: " . Common::SETTING_NAMESPACE_DIR);
    }

    private static function isIgnoredForDocument($annotations) {
        if (!isset($annotations[IDH\Document::class])) {
            return true;
        }
        $document = $annotations[IDH\Document::class][0];
        return $document->ignore;
    }

    private static function getTypeTag($type) {
        $ctype = $type;
        $result = '';
        if (isset($ctype)) {
            if (is_array($ctype) && count($ctype) != 0) {
                $maintype = $ctype[0];
                if (class_exists($maintype)) {
                    $mainref = new \ReflectionClass($maintype);
                    $result .= '<a href="#object_' . str_replace("\\", "_", $mainref->getName()) . '">' . $mainref->getShortName() . '</a>';
                }
                else {
                    $result .= $maintype;
                }
                $size = count($ctype);
                if ($size > 1) {
                    $result .= '&lt;';
                    $endstr = '';
                    for ($i = 1; $i < $size; $i++) {
                        $result .= self::getTypeTag($ctype[$i]);
                        if ($i < $size - 1) {
                            $result .= '&lt;';
                        }
                        $endstr .= '&gt;';
                    }
                    $result .= $endstr;                    
                }
            }
            else if (class_exists($ctype)) {
                $ref = new \ReflectionClass($ctype);
                $result .= '<a href="#object_' . str_replace("\\", "_", $ref->getName()) . '">' . $ref->getShortName() . '</a>';
            }
            else {
                $result .= $ctype;
            } 
        }
        return $result;
    }
}