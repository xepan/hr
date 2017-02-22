<?php

namespace xepan\hr;

/**
* 
*/
class Model_EmployeeDepartmentalAclAssociation extends \xepan\base\Model_Table{
	public $table="employee_app_associations";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Post','post_id');
		$this->hasOne('xepan\base\Epan_InstalledApplication','installed_app_id');
	}
}