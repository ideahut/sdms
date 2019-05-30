<?php
namespace Ideahut\sdms\repository;

use \Ideahut\sdms\object\Page;

interface CrudRepository extends Repository 
{
	
	public function count(); // support: countBy

	public function get($pk); // support: getBy, throw error if result more than one

	public function map(array $order = null, $limit = null); // support: mapBy and map<UniqueField>By, <UniqueField> or pk as map key


	public function save($entity);

	public function saveAll(array $entities);


	public function find(array $order = null); // support: findBy, only get one result, but not throw error if more than one data
	
	public function findAll(array $order = null, Page $page = null); // support: findAllBy, return array/page

	
	public function delete($entity); // support: deleteBy

	public function deleteAll(array $entities);
	
}