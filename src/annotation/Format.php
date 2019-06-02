<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "PROPERTY"})
 */
final class Format
{

	/**
     * @var string
     */
    public $alias = "";

    /**
     * @var boolean
     */
    public $ignore = false;

    /**
     * @var mixed
     */
    public $type = null;

}