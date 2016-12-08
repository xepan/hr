<?php

namespace xepan\hr;

class Model_EmployeeRow extends \xepan\base\Model_Table{
	public $table ="employee_row";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\SalaryAbstract','salary_abstract_id');
		$this->hasOne('xepan\hr\Employee','employee_id');

		$this->addField('total_amount'); // used only for Salary payment 

		$this->hasMany('xepan\hr\SalaryDetail','employee_row_id',null,'SalaryDetail');
		$this->addExpression('total_amout_add')->set(function($m,$q){
			return $q->expr('IFNULL([0],0))',[$m->refSQL('SalaryDetail')->addCondition('calculation_type','add')->sum('amount')]);
		})->type('money');

		$this->addExpression('total_amount_deduction')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('SalaryDetail')->addCondition('calculation_type','deduction')->sum('amount')]);
		})->type('money');
		
		$this->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('[0]-[1]',[$m->getElement('total_amout_add'),$m->getElement('total_amount_deduction')]);
		})->type('money');
	}
}