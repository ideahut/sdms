<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Description
{

	/**
     * @var string
     */
    public $value = "";

}