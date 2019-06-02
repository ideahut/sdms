<?php
namespace Ideahut\sdms\entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityVersion extends Entity
{
	
	/**
     * @ORM\Column(name = "VERSION_", type = "bigint", nullable = false, options = {"unsigned":true})
	 * @ORM\Version
     */
	public $version;
	
}