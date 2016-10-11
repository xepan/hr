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
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/leave-management-grid']);
		$crud->setModel($emp_leave_m);
		$crud->grid->addQuickSearch(['employee']);
		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-employee')->univ()->frameURL('Employee Details',[$this->api->url('xepan_hr_employeedetail'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}
	}
}