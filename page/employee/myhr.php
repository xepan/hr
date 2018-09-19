<?php

namespace xepan\hr;

class page_employee_myhr extends \xepan\base\Page{
	public $title="My HR";
	function init(){
		parent::init();

		$this->app->side_menu->addItem(['My Leave','icon'=>'fa fa-edit'],'xepan_hr_employee_leave')->setAttr(['title'=>'My Leave']);
		$this->app->side_menu->addItem(['My Reimbursement','icon'=>'fa fa-money'],'xepan_hr_employee_reimbursement')->setAttr(['title'=>'My Reimbursement']);
		$this->app->side_menu->addItem(['Salary Slip','icon'=>'fa fa-money'],'xepan_hr_employee_salaryslip')->setAttr(['title'=>'Your Salary Slip']);
		$this->app->side_menu->addItem(['Account Deactivate','icon'=>'fa fa-power-off'],'xepan_hr_employee_accountdeactivate')->setAttr(['title'=>'Account Deactivate Request']);
	}
}