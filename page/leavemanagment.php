<?php

namespace xepan\hr;
/**
*
*/
class page_leavemanagment extends \xepan\base\Page{
	public $title = "Employee Leave Managment";

	function init(){
		parent::init();
				
		$from_date = $this->app->stickyGET('from_date')?:date('Y-m-01',strtotime($this->app->now));
		$to_date = $this->app->stickyGET('to_date')?:date('Y-m-t',strtotime($this->app->now));

		$filter_form = $this->add('Form');
		$filter_form->add('xepan\base\Controller_FLC')
					->makePanelsCoppalsible()
					->layout([
						'from_date'=>'Filter~c1~4~closed',
						'to_date'=>'Filter~c2~4',
						'FormButtons~'=>''
					]);

		$filter_form->addField('DatePicker','from_date')->set($from_date);
		$filter_form->addField('DatePicker','to_date')->set($to_date);
		$filter_form->addSubmit('Filter')->addClass('btn btn-primary');

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$emp_leave_m->addCondition('status','<>','Draft');
		$emp_leave_m->getElement('status')->defaultValue('Submitted');
		$emp_leave_m->getElement('emp_leave_allow_id')->caption('Leave');

		$emp_leave_m->addCondition('from_date','>=',$from_date);
		$emp_leave_m->addCondition('to_date','<',$this->app->nextDate($to_date));

		$crud = $this->add('xepan\hr\CRUD');
		//$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/leave-management-grid']);
		$crud->form->add('xepan\base\Controller_FLC')
					->addContentSpot()
					->makePanelsCoppalsible()
					->layout([
						'employee_id~Employee'=>'Employee Leave~c1~4',
						'emp_leave_allow_id~Leave'=>'c2~4',
						'from_date'=>'c3~2',
						'to_date'=>'c4~2',
						'narration'=>'c6~12',
						'FormButtons~'=>'c5~2'
					]);

		if($filter_form->isSubmitted()){
			$crud->js()->reload(['from_date'=>$filter_form['from_date'],'to_date'=>$filter_form['to_date']])->execute();
		}
		$emp_leave_m->getElement('emp_leave_allow')->caption('Leave Type');
		$crud->setModel($emp_leave_m,
							['created_by_id','created_by','employee_id','employee','emp_leave_allow_id','emp_leave_allow','from_date','to_date','narration'],
							['employee','emp_leave_allow','from_date','to_date','status','no_of_leave','narration']
						);

		$crud->add('xepan\base\Controller_MultiDelete');
		$crud->grid->addQuickSearch(['employee']);
		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-employee')->univ()->frameURL('Employee Details',[$this->api->url('xepan_hr_employeedetail'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}

		if($crud->isEditing()){
			$form = $crud->form;
			$field_employee = $form->getElement('employee_id');
			$field_leave_allow = $form->getElement('emp_leave_allow_id');
			if($emp_id = $_GET['s_emp_id']){
				$field_leave_allow->getModel()->addCondition('employee_id',$emp_id);
			}

			$field_employee->js('change',$form->js()->atk4_form('reloadField','emp_leave_allow_id',[$this->app->url(null,['cut_object'=>$field_leave_allow->name]),'s_emp_id'=>$field_employee->js()->val()]));
			//$field_employee->js('change',$field_leave_allow->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$field_leave_allow->name]),'s_emp_id'=>$field_employee->js()->val()]));
		}                                                                                                                    

		$crud->grid->addFormatter('employee','template')
				->setTemplate('<a href="#" class="do-view-employee" data-id="{$employee_id}">{$employee}</a>','employee');

		$crud->grid->addPaginator($ipp=50);
		$crud->grid->addSno();
		$crud->grid->removeColumn('status');
		$crud->grid->removeAttachment();

		$crud->grid->addButton('Leave Config')
			->addClass('btn btn-primary')
			->js('click')->univ()->frameURL($this->app->url('xepan_hr_configleave'));
	}
}