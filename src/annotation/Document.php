<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY"})
 */
final class Document
{

	/**
     * @var boolean
     */
    public $ignore = false;

	/**
     * @var mixed
     */
    public $description;

	/**
     * @var mixed
     */
    public $type;

    /**
     * @var array<Ideahut\sdms\annotation\Parameter>
     */
    public $parameter;

    /**
     * @var mixed
     */
    public $body;

    /**
     * @var mixed
     */
    public $result;

}