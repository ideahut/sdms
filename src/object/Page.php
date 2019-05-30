<?php
namespace Ideahut\sdms\object;

/**
 * @FORMAT(show_null=false)
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
     * @DESCRIPTION Indeks halaman
     * @TYPE integer
	 * @FORMAT
     */
	public $index = 1;

	/**
	 * @DESCRIPTION Jumlah data per halaman
	 * @TYPE integer
	 * @FORMAT
	 */
	public $size = 20;
	
	/**
	 * @DESCRIPTION Total halaman
	 * @TYPE integer
	 * @FORMAT
	 */
	public $total;
	
	/**
	 * @DESCRIPTION Total data yang tersedia, jika bernilai False maka total halaman tidak tersedia
	 * @TYPE boolean / integer
	 * @FORMAT
	 */
	public $count = false;
	
	/**
	 * @DESCRIPTION Daftar data
	 * @TYPE Array[object]
	 * @FORMAT
	 */
	public $data; // Array Index
	
	/**
	 * @DESCRIPTION Informasi tambahan
	 * @TYPE Array[{key: value}]
	 * @FORMAT
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
