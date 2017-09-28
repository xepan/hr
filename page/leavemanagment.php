<?php

namespace xepan\hr;
/**
*
*/
class page_leavemanagment extends \xepan\base\Page{
	public $title = "Employee Leave Managment";

	function init(){
		parent::init();

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$emp_leave_m->addCondition('status','<>','Draft');
		$emp_leave_m->getElement('status')->defaultValue('Submitted');

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/leave-management-grid']);
		$crud->setModel($emp_leave_m,
							['created_by_id','created_by','employee_id','employee','emp_leave_allow_id','emp_leave_allow','from_date','to_date'],
							['created_by_id','created_by','employee_id','employee','emp_leave_allow_id','emp_leave_allow','from_date','to_date','status','month','year','month_leaves','leave_type','month_from_date','month_to_date','no_of_leave']
						);

		$crud->add('xepan\base\Controller_MultiDelete');
		$crud->grid->addQuickSearch(['employee']);
		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-employee')->univ()->frameURL('Employee Details',[$this->api->url('xepan_hr_employeedetail'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}

		if($crud->isEditing()){
			$form = $crud->form;
			$employee_field = $form->getElement('employee_id');
			$leave_allow_id = $form->getElement('emp_leave_allow_id');
			$employee_field->js('change');
		}

	}
}