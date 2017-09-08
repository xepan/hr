<?php

namespace xepan\hr;


/**
* 
*/
class page_leavemanagment extends \xepan\base\Page{
	public $title= "Employee Leave Managment";
	function init(){
		parent::init();
		// $emp_id= $this->api->stickyGET('employee_id');
		// $employee = $this->add('xepan\hr\Model_Employee');
		// $employee->addCondition('status','Active');
		// $form=$this->add('Form',null,null,['form/empty']);
		// $emp = $form->addField('DropDown','employee');
		// $emp->setModel($employee);
		// $form->addSubmit('Search')->addClass('btn btn-info');

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$emp_leave_m->addCondition('status','<>','Draft');
		
		// if($emp_id){
		// 	$emp_leave_m->addCondition('employee_id',$emp_id);
		// }

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

		// if($form->isSubmitted()){
		// 	return $form->js(null,$crud->js()->reload([
		// 											'employee_id'=>$form['employee']
		// 											]
		// 											))->execute();
		// }
	}
}