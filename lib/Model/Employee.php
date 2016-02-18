<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User');
		$emp_j->hasOne('xepan\hr\Post');
		$emp_j->addField('is_active')->type('boolean')->defaultValue(true);


		$this->addCondition('type','employee');


	}
}
