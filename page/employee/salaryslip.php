<?php

namespace xepan\hr;

class page_employee_salaryslip extends \xepan\base\Page{
	public $title="Salary Slips";
	function init(){
		parent::init();

		$this->add('xepan\hr\View_SalaryLedger',['employee_id'=>$this->app->employee->id,'show_balance'=>false]);
	}
}