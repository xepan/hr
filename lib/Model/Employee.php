<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\hr\Post');
		$emp_j->addField('is_active')->type('boolean')->defaultValue(true);

		$user_j = $this->join('user.contact_id');
		$user_j->addField('username');
		$user_j->addField('password');
		$user_j->addField('user_id','id');

		$this->addCondition('type','employee');

	}
}
