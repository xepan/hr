<?php

namespace xepan\hr;

class page_employee_leave extends \xepan\hr\page_employee_myhr{
	public $title="My Leave";
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$new_leave_tab = $tabs->addTab('New Leave');

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		// $emp_leave_m->addCondition('employee_id',$this->app->employee->id);

		$f = $new_leave_tab->add('Form');
		$allow_leave_f = $f->addField('Dropdown','allow_leave');
		$allow_leave_f->setEmptytext('Please Select');
		$allow_leave_f->setModel('xepan\hr\Model_Employee_LeaveAllow');
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');

		$f->addSubmit('Get Leave')->addClass('btn btn-primary');

		$draft_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$draft_leave_m->addCondition('created_by_id',$this->app->employee->id);
		$draft_leave_m->addCondition('status',"Draft");
		$draft_grid = $new_leave_tab->add('xepan\hr\CRUD',null,null,['view/employee/my-leave-management-grid']);
		$draft_grid->setModel($draft_leave_m);
		// $draft_grid->addQuickSearch(['employee']);
		
		if($f->isSubmitted()){
			$allow_leave_m = $this->add('xepan\hr\Model_Employee_LeaveAllow');
			$allow_leave_m->load($f['allow_leave']);
			$date = $this->app->my_date_diff($f['from_date'],$f['to_date']);
			
			if(!$allow_leave_m['allow_over_quota']){
				if($date['days'] > $allow_leave_m['no_of_leave']){
					$f->displayError('to_date','Not allow more than employee Leave');
				}
			}

			$emp_leave_m['emp_leave_allow_id']=$allow_leave_m->id;		
			$emp_leave_m['from_date']=$f['from_date'];		
			$emp_leave_m['to_date']=$f['to_date'];
			$emp_leave_m->save();		

			$js =[
					$f->js()->univ()->successMessage('Done'),
					$draft_grid->js()->reload()
				];	
			$f->js(null,$js)->reload()->execute();
		}


		$approved_tab = $tabs->addTab('Approved Leave');
		$approved_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$approved_leave_m->addCondition('created_by_id',$this->app->employee->id);
		$approved_leave_m->addCondition('status',"Approved");

		$apprved_grid = $approved_tab->add('xepan\hr\Grid',null,null,['view/employee/my-approved-leave-management-grid']);
		$apprved_grid->setModel($approved_leave_m);
		$apprved_grid->addQuickSearch(['employee']);



	}
}