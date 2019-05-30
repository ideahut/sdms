<?php
namespace Ideahut\sdms\entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityTimeVersion extends EntityTime
{
	
	/**
     * @ORM\Column(name = "VERSION_", type = "bigint", nullable = false, options = {"unsigned":true})
	 * @ORM\Version
     * @FORMAT
     */
	public $version;
	
}
