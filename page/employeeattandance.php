<?php
namespace xepan\hr;

class page_employeeattandance extends \xepan\base\Page{
	public $title = "Employee Attandance";

	function init(){
		parent::init();
		
		$employee_attandance_m = $this->add('xepan\hr\Model_EmployeeAttandance');
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($employee_attandance_m);
	}
}