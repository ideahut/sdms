<?php
namespace Ideahut\sdms\entity;

interface EntitySoftDelete
{
    public function getDeleted();

	public function setDeleted(boolean $deleted);
	
}