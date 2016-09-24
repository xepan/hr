<?php

namespace xepan\hr;

class Model_PaymentAndDeduction extends \xepan\base\Model_Table{
	public $table = "payment_and_deduction";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\SalaryTemplate','salary_template_id');
		$this->hasOne('xepan\hr\Employee','employee_id');
		
		$this->addField('name');
		$this->addField('type')->setValueList(['Add'=>'Add','Deduct'=>'Deduct']);
		$this->addField('amount');
	}
}