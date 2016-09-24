<?php

namespace xepan\hr;

class Model_Leave extends \xepan\base\Model_Table{
	public $table = "leave";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\LeaveTemplate','leave_template_id');

		$this->addField('name');
		$this->addField('type');
		$this->addField('allowed');
		$this->addField('deduction');		
		
		$this->hasMany('xepan\hr\EmployeeAllowedLeave','leave_id',null,'EmployeeAllowedLeave');
	}
}