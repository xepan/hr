<?php

namespace xepan\hr;

class Employee extends xepan\base\Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\Post');
		$this->addField('status')->enum(['Active','Left']);

	}
}