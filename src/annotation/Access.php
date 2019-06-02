<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Access
{

	/**
     * @var boolean
     */
    public $public = false;

    /**
     * @var boolean
     */
    public $login = true;

}