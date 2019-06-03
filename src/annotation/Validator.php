<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
final class Validator
{

	/**
	 * @var array<Ideahut\sdms\annotation\ClassMethod>
     */
    public $value;

}