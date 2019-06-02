<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Parameter
{

	/**
	 * @var string
     */
    public $name;

    /**
	 * @var mixed
     */
    public $type;

    /**
	 * @var mixed
     */
    public $description;

}