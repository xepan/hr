<?php

namespace xepan\hr;

/**
* 
*/
class Model_Salary extends \xepan\base\Model_Table{
	public $table ="salary";
	public $actions = ['*'=>['view','edit','delete']];
 	public $acl_type = "Salary";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);
		$this->addField('name');
		$this->addField('type')->enum(['Salary','Allowance','Deduction']);
		$this->addField('add_deduction')->enum(['add','deduction','dummy']);
		$this->addField('unit')->enum(['Month','Leave']);
		$this->addField('order')->type('int');
		$this->addField('default_value');

		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_id');
		$this->hasMany('xepan\hr\Employee_Salary','salary_id');
		
		$this->setOrder('order','asc');

		$this->addHook('beforeSave',$this);
	}
	
	function beforeSave(){

		preg_match_all("/{(.*?)}/", $this['default_value'], $all_match);
		
		$system_calculated_factor = [
										'Presents'=>'Presents',
										'PaidLeaves'=>'PaidLeaves',
										'UnPaidLeaves'=>'UnPaidLeaves',
										'Absents'=>'Absents',
										'PaidDays'=>'PaidDays',
										'TotalWorkingDays'=>'TotalWorkingDays'
									];

		foreach ($all_match[1] as $key => $name) {
			$name = trim($name);
			if(isset($system_calculated_factor[$name])) continue;
			
			$salary_model = $this->add('xepan\hr\Model_Salary')
					->addCondition('order','<',$this['order'])
					->addCondition('name',$name)
					;
			$salary_model->tryLoadany();
			if(!$salary_model->loaded())
				throw $this->Exception("(".$name.') salary must define, before using it into any expression/calculation','ValidityCheck')->setField('name');
		}
	}

}