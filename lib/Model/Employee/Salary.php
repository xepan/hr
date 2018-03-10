<?php

namespace xepan\hr;

class Model_Employee_Salary extends \xepan\base\Model_Table{
	public $table ="employee_salary";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Salary','salary_id');
		$this->addField('amount')->hint('leave empty to get salary default value');
		$this->addField('unit')->enum(['monthly']);
		
		$this->addExpression('add_deduction')->set($this->refSQL('salary_id')->fieldQuery('add_deduction'));
		$this->addExpression('salary_order')
			->set($this->refSQL('salary_id')->fieldQuery('order'));

		$this->setOrder('salary_order','asc');
		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if(!$this['amount']){
			$salary= $this->add("xepan\hr\Model_Salary")->tryLoad($this['salary_id']);
			if($salary->loaded()){
				$this['amount'] = $salary['default_value'];
			}
		}
		
	}
}