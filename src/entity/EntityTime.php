<?php
namespace Ideahut\sdms\entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityTime extends Entity
{
	
	/**
     * @ORM\Column(name = "CREATED_AT_", type = "datetime", nullable = false)
     */
	public $createdAt;

	/**
     * @ORM\Column(name = "UPDATED_AT_", type = "datetime", nullable = false)
     */
	public $updatedAt;
	
}
