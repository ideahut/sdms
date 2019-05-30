<?php
namespace Ideahut\sdms\object;

/**
 * @DOCUMENT(ignore=true)
 * @FORMAT(show_null=false)
 */
class Admin
{
	/**
     * @DESCRIPTION Class Entity
     * @TYPE string
	* @FORMAT
     */
	public $entity;

	/**
     * @DESCRIPTION Primary Key
     * @TYPE any
	* @FORMAT
     */
	public $pk;

	/**
     * @DESCRIPTION Nama field yang akan menjadi key untuk map
     * @TYPE any
	* @FORMAT
     */
	public $mapkey;

	/**
     * @DESCRIPTION Page
     * @TYPE Array[<a href="#entity_Page">Page</a>]
	* @FORMAT(type=\Ideahut\sdms\object\Page)
     */
	public $page;

	/**
     * @DESCRIPTION Offset awal data
     * @TYPE integer
     * @FORMAT
     */
	public $start;

	/**
     * @DESCRIPTION Maksimum jumlah data
     * @TYPE integer
     * @FORMAT
     */
	public $limit;

	/**
     * @DESCRIPTION Filter data. Untuk tipe string gunakan pemisah '|'.
     * @TYPE string atau Array[{key: value}]
     * @FORMAT
     */
	public $filter;

	/**
     * @DESCRIPTION Pengurutan data (DESC tambahkan '-' di depan nama field). Untuk tipe string gunakan pemisah ','.
     * @TYPE string atau Array[fields]
     * @FORMAT
     */
	public $order;

	/**
     * @DESCRIPTION Pengelompokan data. Untuk tipe string gunakan pemisah ','. 
     * @TYPE string atau Array[fields]
     * @FORMAT
     */
	public $group;

	/**
     * @DESCRIPTION Daftar field yang akan diambil. Untuk tipe string gunakan pemisah ','.
     * @TYPE string atau Array[fields]
     * @FORMAT
     */
	public $field;

	/**
     * @DESCRIPTION Nilai dari field yang akan diproses. Untuk tipe string gunakan pemisah '|'.
     * @TYPE string atau Array[fields]
     * @FORMAT
     */
	public $value;

}