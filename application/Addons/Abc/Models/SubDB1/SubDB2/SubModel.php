<?php
namespace Addons\Abc\Models\SubDB1\SubDB2;

class SubModel extends \Base\Model {
	
	public function getRecordsFromTable() {
		return array(
			'Record 1',
			'Record 2',
			'Record 3'
		);
	}
	
}