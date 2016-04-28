<?php

namespace xepan\hr;

class Model_Employee_Movement extends \xepan\base\Model_Table{
	public $table="employee_movement";
	public $acl=false;

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('time');
		$this->addField('type')->enum(['Attandance','Movement']);
		$this->addField('direction');
		$this->addField('reason')->enum(['Personal Outing', 'Official Outing', 'Other']);
		$this->addField('narration')->type('text');
	}
}