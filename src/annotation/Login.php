<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Login
{

	/**
     * @var boolean
     */
    public $value = true;

}