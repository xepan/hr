<?php

namespace xepan\hr;

class Model_SalaryDetail extends \xepan\base\Model_Table{
	public $table ="salary_detail";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\EmployeeRow','employee_row_id');
		$this->hasOne('xepan\hr\Salary','salary_id');
		$this->addField('amount');
		$this->addExpression('calculation_type')->set($this->refSQL('salary_id')->fieldQuery('add_deducat'));
		
	}
}