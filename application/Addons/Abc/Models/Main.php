<?php
namespace Addons\Abc\Models;

class Main extends \Base\Model {
	
	private $records;
	
	public function __construct()
	{
		$this->records = array(
			1,2,3,4,5
		);
	}
	
	public function getRecords() {
		return $this->records;
	}
	
}