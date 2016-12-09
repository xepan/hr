<?php

namespace xepan\hr;

/**
* 
*/
class Model_Salary extends \xepan\base\Model_Table{
	public $table ="salary";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('type')->enum(['Salary','Allowance','Deduction']);
		$this->addField('add_deducat')->enum(['add','deduction','dummy']);
		$this->addField('unit')->enum(['Month','Leave']);
		$this->addField('order')->type('int');
		$this->addField('default_value');

		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_id');
		$this->hasMany('xepan\hr\Employee_Salary','salary_id');
		
		$this->setOrder('order','asc');
	}
	
}