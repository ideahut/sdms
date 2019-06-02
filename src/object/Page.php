<?php
namespace Ideahut\sdms\object;

use Ideahut\sdms\annotation as IDH;

/**
 * @IDH\Format
 */
class Page 
{

	public function __construct() {
    	$argv = func_get_args();
    	$narg = func_num_args();
		if ($narg > 0) {
			$this->index = $argv[0];
		}
		if ($narg > 1) {
			$this->size = $argv[1];
		}
		if ($narg > 2) {
			$this->count = $argv[2];
		}
    }

    /**
     * @IDH\Document(description="Page index", type="integer")
     */
	public $index = 1;

	/**
	 * @IDH\Document(description="Maximum rows per page", type="integer")
     */
	public $size = 20;
	
	/**
	 * @IDH\Document(description="Page total", type="integer")
     */
	public $total;
	
	/**
	 * @IDH\Document(description="Total data or flag (true/false) to count total", type="integer / boolean")
     */
	public $count = false;
	
	/**
	 * @IDH\Document(description="List of data", type="Array[data]")
     */
	public $data; // Array Index
	
	/**
	 * @IDH\Document(description="Information", type="Array[{key: value}]")
     */
	public $info; // Array Associative


	public function setRecords($records) {
        $this->count = (int)$records;
		if ($this->count < 0) {
			$this->count = 0;
		}
		$this->total = $this->count > 0 ? ceil((float) $this->count / $this->size) : 0;
		if ($this->total == 0) {
			$this->index = 1;
		}
    }
		
}
