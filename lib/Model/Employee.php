<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User');
		$emp_j->hasOne('xepan\hr\Department','department_document_id');
		$emp_j->hasOne('xepan\hr\Post','post_document_id');

		$this->addCondition('type','Employee');
	}
}
