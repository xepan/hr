<?php

namespace xepan\hr;

class Model_EmployeeAllowedLeave extends \xepan\base\Model_Table{
	public $title = "employee_allowed_leave";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');	
		$this->hasOne('xepan\hr\Leave','leave_id');	
	}
}