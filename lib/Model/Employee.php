<?php

namespace xepan\hr;

class Employee extends xepan\base\Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\Post');
		$emp_j->addField('status')->enum(['Active','Left']);

	}
}
