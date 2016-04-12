<?php

namespace xepan\hr;

class Model_Employee_Movement extends \xepan\base\Model_Table{
	public $table="employee_movement";
	public $acl=false;

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('date')->type('date')->defaultValue(date('Y-m-d'));
		$this->addField('time')->defaultValue(date('H:i:s'));
		$this->addField('type')->enum(['Attandance','Movement']);
		$this->addField('direction');
	}

}