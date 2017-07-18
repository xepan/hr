<?php

namespace xepan\hr;

/**
* 
*/
class page_employee_deactivaterequest extends \xepan\base\Page{

	public $title = "Employee Deactivate Requests";
	function init(){
		parent::init();
		
		$emp = $this->add('xepan\hr\Model_Employee')
					->addCondition('status','DeactivateRequest');

		$crud=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_hr_employeedetail'],null,['view/employee/employee-grid']);
		$crud->grid->addPaginator(50);
		$crud->setModel($emp);
		$crud->add('xepan\base\Controller_MultiDelete');			
	}
}