<?php
namespace Ideahut\sdms\object;

use Ideahut\sdms\annotation as IDH;

/**
 * @IDH\Format
 */
class Admin
{
	/**
      * @IDH\Document(description="Class Entity", type="string")
      */
	public $entity;

	/**
      * @IDH\Document(description="Primary Key", type="mixed")
      */
	public $pk;

	/**
      * @IDH\Document(description="Field name as map key", type="string")
      */
	public $mapkey;

	/**
      * @IDH\Document(description="Page", type=Page::class)
      * @IDH\Format(type=Page::class)
      */
	public $page;

     /**
      * @IDH\Document(description="Start offset", type="integer")
      */
	public $start;

	/**
      * @IDH\Document(description="Limit data", type="integer")
      */
	public $limit;

     /**
      * @IDH\Document(description="Filter, for string use separator '|'", type="string / Array[{key: value}]")
      */
	public $filter;

     /**
      * @IDH\Document(description="Order, for  descending add '-' in front of field name. For string use separator ','", type="string / Array[fields]")
      */
	public $order;

     /**
      * @IDH\Document(description="Group, for string use separator ','", type="string / Array[fields]")
      */
	public $group;

     /**
      * @IDH\Document(description="Specific field names to retrieve, for string use separator ','", type="string / Array[fields]")
      */
	public $field;

     /**
      * @IDH\Document(description="Value of field, for string use separator '|'", type="string / Array[fields]")
      */
	public $value;

}