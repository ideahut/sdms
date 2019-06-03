<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "ANNOTATION"})
 */
final class ClassMethod
{
	/**
	 * @var mixed
     */
    public $class;

    /**
	 * @var string
     */
    public $method;

}