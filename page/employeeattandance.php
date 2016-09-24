<?php
namespace xepan\hr;

class page_employeeattandance extends \xepan\base\Page{
	public $title = "Employee Attandance";

	function init(){
		parent::init();
		
		$employee_attandance_m = $this->add('xepan\hr\Model_EmployeeAttandance');
		$employee_attandance_m->addCondition('created_at','>',$this->app->today);
		
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($employee_attandance_m, ['employee_id','leave_id','date'],['employee','leave','date']);	
	}
}