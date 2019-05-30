<?php
namespace Ideahut\sdms\entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class EntityBigIntIdTime extends EntityTime
{

	/**
     * @ORM\Column(name = "ID_", type = "bigint", nullable = false, options = {"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy = "IDENTITY")
     * @FORMAT 
	 */
	public $id;

}