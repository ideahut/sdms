<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"METHOD", "ANNOTATION"})
 */
final class Method
{
	/**
	 * "GET", "POST", "PUT", "DELETE", "OPTIONS", "HEAD", "TRACE", "PATCH", "CONNECT"
	 *
     * @var array<string>     
     */
    public $value = "GET";
}