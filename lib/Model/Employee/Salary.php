<?php

namespace xepan\hr;

class Model_Employee_Salary extends \xepan\base\Model_Table{
	public $table ="employee_salary";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Salary','salary_id');
		$this->addField('amount')->type('int');
		$this->addField('unit')->enum(['monthly']);
	}
}