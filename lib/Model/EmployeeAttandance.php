<?php

namespace xepan\hr;

class Model_EmployeeAttandance extends \xepan\base\Model_Table{
	public $table = "employee_attandance";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Leave','leave_id');
		$this->hasOne('xepan\hr\Employee','employee_id');

		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('date')->type('datetime');		
	}
}