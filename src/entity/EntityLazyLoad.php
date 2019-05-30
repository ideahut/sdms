<?php
namespace Ideahut\sdms\entity;

interface EntityLazyLoad
{
    public function doLazyLoad();
	
	public function isLazyLoaded();
	
}