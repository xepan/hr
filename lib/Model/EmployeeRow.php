<?php

namespace xepan\hr;

class Model_EmployeeRow extends \xepan\base\Model_Table{
	public $table ="employee_row";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\SalaryAbstract','salary_abstract_id');
		$this->hasOne('xepan\hr\Employee','employee_id');

		$this->addField('total_amount')->type('money')->defaultValue(0); // used only for Salary payment 

		// system calculated fields
		$this->addField('presents');
		$this->addField('paid_leaves');
		$this->addField('unpaid_leaves');
		$this->addField('absents');
		$this->addField('paiddays');
		$this->addField('total_working_days');
		$this->addField('officialholidays');
		$this->addField('extraworkingdays');
		$this->addField('extraworkinghours');
		$this->addField('paidleavesonholiday');
		$this->addField('unpaidleavesonholiday');

		// end of system calculated fields

		$this->addExpression('reimbursement_amount',function($m,$q){
			return $m->add('xepan\hr\Model_SalaryDetail')
					->addCondition('employee_row_id',$m->getElement('id'))
					->addCondition('is_reimbursement',true)
				->sum('amount');
		});

		$this->addExpression('deduction_amount',function($m,$q){
			return $m->add('xepan\hr\Model_SalaryDetail')
					->addCondition('employee_row_id',$m->getElement('id'))
					->addCondition('is_deduction',true)
				->sum('amount');
		});

		$this->hasMany('xepan\hr\SalaryDetail','employee_row_id',null,'SalaryDetail');
		
		$sal = $this->add('xepan\hr\Model_Salary');
		foreach ($sal->getRows() as $s) {
			$this->addExpression($this->app->normalizeName($s['name']))->set(function($m,$q)use($s){
				return $q->expr('ROUND([0],2)',[$m->refSQL('SalaryDetail')->addCondition('salary_id',$s['id'])->fieldQuery('amount')]);
			});
		}


		$this->addExpression('total_amout_add')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('SalaryDetail')->addCondition('calculation_type','add')->sum('amount')]);
		})->type('money');


		$this->addExpression('total_amount_deduction')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('SalaryDetail')->addCondition('calculation_type','deduction')->sum('amount')]);
		})->type('money');
		
		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('round(([0]-[1]),2)',[$m->getElement('total_amout_add'),$m->getElement('total_amount_deduction')]);
		})->type('money');


		$this->addExpression('created_at')->set($this->refSQL('salary_abstract_id')->fieldQuery('created_at'))->type('date');

		$this->addHook('beforeDelete',[$this,'deleteSalaryDetail']);
	}

	function deleteSalaryDetail(){
		foreach($this->ref('SalaryDetail') as $sl_det){
			$sl_det->delete();
		}
	}

	function addSalaryDetail($salary_detail = []){
		if(!$this->loaded()) throw new \Exception("model must loaded", 1);
		
		foreach ($salary_detail as $salary_id => $amount) {
			$sd = $this->add('xepan\hr\Model_SalaryDetail');
			$sd->addCondition('employee_row_id',$this->id)
				->addCondition('salary_id',$salary_id);
			$sd->tryLoadAny();
			$sd['amount'] = $amount;
			$sd->saveAndUnload();
		}
	}
}