<?php

namespace xepan\hr;


/**
* 
*/
class page_leavemanagment extends \xepan\base\Page{
	public $title= "Employee Leave Managment";
	function init(){
		parent::init();

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$crud = $this->add('xepan\hr\CRUD');
		$crud->setModel($emp_leave_m);
		$crud->grid->addQuickSearch(['employee']);
	}
}