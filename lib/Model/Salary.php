<?php

namespace xepan\hr;

/**
* 
*/
class Model_Salary extends \xepan\base\Model_Table{
	public $table ="salary";
	public $actions = ['All'=>['view','edit','delete']];
 	public $acl_type = "Salary";

	function init(){
		parent::init();

		$this->addField('name')->caption('Salary');
		$this->addField('type')->enum(['Salary','Allowance','Deduction']);
		$this->addField('add_deduction')->enum(['add','deduction','dummy']);
		$this->addField('unit')->enum(['Month','Leave']);
		$this->addField('order')->type('int');
		$this->addField('default_value');
		$this->addField('is_reimbursement')->type('boolean')->hint('Please select,If this salary type is reimbursement. For get the value of approved reimbursement of an employee')->defaultValue(false);
		$this->addField('is_deduction')->type('boolean')->hint('Please select, if this salary type is deduction. For get the value of deduction which is charged to an employee')->defaultValue(false);
		
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_id');
		$this->hasMany('xepan\hr\Employee_Salary','salary_id');
		
		$this->setOrder('order','asc');

		// $this->is([
		// 	'is_reimbursement|unique',
		// 	'is_deduction|unique'
		// 	]);

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
										'TotalWorkingDays'=>'TotalWorkingDays',
									];
		
		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_reimbursement_affect_salary'=>"Line",
						],
			'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$reimbursement_config_model->tryLoadAny();

		if($reimbursement_config_model['is_reimbursement_affect_salary'] === "yes")
				$system_calculated_factor['Reimbursement']= 'Reimbursement';

		$deduction_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_deduction_affect_salary'=>"Line",
						],
			'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$deduction_config_model->tryLoadAny();

		if($deduction_config_model['is_deduction_affect_salary'] === "yes")
			$system_calculated_factor['Deduction']= 'Deduction';

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