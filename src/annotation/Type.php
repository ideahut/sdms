<?php
namespace Ideahut\sdms\annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD", "PROPERTY", "ANNOTATION"})
 */
final class Type
{
	/**
	 * Untuk nested type maka diisi dengan array type
	 * Contoh:
	 *		$value = [Page:class, User:class] => berarti object Page yang berisi daftar User
	 *
	 * @var mixed
     */
    public $value;
}