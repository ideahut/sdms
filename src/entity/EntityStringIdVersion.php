<?php
namespace Ideahut\sdms\entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityStringIdVersion extends EntityVersion
{

	/**
     * @ORM\Column(name="ID_", type = "string", nullable = false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "UUID")  
     */
	public $id;

}