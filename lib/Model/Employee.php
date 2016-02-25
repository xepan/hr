<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User');
		$emp_j->hasOne('xepan\hr\Department','department_id');
		$emp_j->hasOne('xepan\hr\Post','post_id');

		$emp_j->hasMany('xepan\hr\Qualificaton','employee_id',null,'Qualificatons');
		$emp_j->hasMany('xepan\hr\Experience','employee_id',null,'Experiences');
		
		$this->addCondition('type','Employee');
	}
}
