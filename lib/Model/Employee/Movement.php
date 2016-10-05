<?php

namespace xepan\hr;

class Model_Employee_Movement extends \xepan\base\Model_Table{
	public $table="employee_movement";
	public $acl=false;

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id')->sortable(true);
		$this->addField('movement_at')->type('datetime')->sortable(true);
		$this->addExpression('date')->set('DATE(movement_at)');
		$this->addField('direction')->enum(['In','Out']);
		$this->addField('reason')->enum(['Personal Outing', 'Official Outing', 'Other']);
		$this->addField('narration')->type('text');
		
		$this->addExpression('employee_in_time')->set($this->refSQL('employee_id')->fieldQuery('in_time'));
		$this->addExpression('employee_out_time')->set($this->refSQL('employee_id')->fieldQuery('out_time'));
		
	}
}