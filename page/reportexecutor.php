<?php
namespace xepan\hr;

class page_reportexecutor extends \xepan\hr\page_configurationsidebar{
	public $title = "Report Executor";
	public $widget_list = [];

	function init(){
		parent::init();

		$report_executor = $this->add('xepan\hr\Model_ReportExecutor');
		$report_executor->getElement('time_span')->caption('Report Type');
		$report_executor->getElement('schedule_date')->caption('Next Schedule');
		$report_executor->getElement('data_from_date')->caption('date range');


		$crud = $this->add('xepan\hr\CRUD');
		// ,null,null,['page\reportexecutor']
		if($crud->isEditing()){
			$crud->form->setLayout('form\reportexecutor');
		}

		$crud->setModel($report_executor,['employee','post','department','widget','time_span','financial_month_start','starting_from_date','data_range'],['time_span','schedule_date','data_from_date','data_to_date','status']);
		$crud->grid->addSno();
		$crud->grid
			->addFormatter('data_from_date','template')
			->setTemplate('<span>{$data_from_date} To {$data_to_date}</span>','data_from_date');
		$crud->grid->removeColumn('status');
		$crud->grid->removeColumn('data_to_date');
		$crud->grid->removeAttachment();

		if($crud->isEditing()){
			$this->bindConditionalShow($crud->form);
			if($crud->model->id)
				$this->setValuesInField($crud->model->id,$crud);

			$employee_field = $crud->form->getElement('employee');
			$employee_field->validate_values=false;
			$employee_field->setAttr(['multiple'=>'multiple']);
			$employee_field->setModel('xepan\hr\Model_Employee');

			$post_field = $crud->form->getElement('post');
			$post_field->setAttr(['multiple'=>'multiple']);
			$post_field->setModel('xepan\hr\Model_Post');
			
			$department_field = $crud->form->getElement('department');
			$department_field->setAttr(['multiple'=>'multiple']);
			$department_field->setModel('xepan\hr\Model_Department');
			
			$widget_field = $crud->form->getElement('widget');
			$widget_field->setAttr(['multiple'=>'multiple']);			
			$widget_field->setValueList($this->collectWidgets());
		}
	}

	function setValuesInField($id,$crud){
		$report_executor_m = $this->add('xepan\hr\Model_ReportExecutor')->load($id);
		$temp = [];
		$temp = explode(',', $report_executor_m['employee']);	

		$temp1 = [];
		$temp1 = explode(',', $report_executor_m['post']);																																														

		$temp2 = [];
		$temp2 = explode(',', $report_executor_m['department']);

		$temp3 = [];
		$temp3 = explode(',', $report_executor_m['widget']);
		
		$crud->form->getElement('employee')->set($temp)->js(true)->trigger('changed');
		$crud->form->getElement('post')->set($temp1)->js(true)->trigger('changed');
		$crud->form->getElement('department')->set($temp2)->js(true)->trigger('changed');
		$crud->form->getElement('widget')->set($temp3)->js(true)->trigger('changed');
	}

	function bindConditionalShow($form){
		$time_span_field = $form->getElement('time_span');
		$time_span_field->js(true)->univ()->bindConditionalShow([
				'Daily'=>['starting_from_date'],
				'Weekely'=>['starting_from_date'],
				'Fortnight'=>['starting_from_date'],
				'Monthly'=>['starting_from_date','data_range'],
				'Quarterly'=>['starting_from_date','financial_month_start'],
				'Halferly'=>['starting_from_date','financial_month_start'],
				'Yearly'=>['starting_from_date','financial_month_start']
			],'div.atk-form-row');
	}

	function collectWidgets(){
		$this->app->hook('widget_collection',[&$this->widget_list]);
		$emp_scope = $this->app->employee->ref('post_id')->get('permission_level');

		$enum_array=[];
		foreach ($this->widget_list as $widget) {
			$to_add=false;
			switch($emp_scope) {
				case 'Global':
					$to_add =true;					
					break;
				case 'Department':
					if(in_array($widget['level'], ['Individual','Department'])) $to_add=true;
					break;
				case 'Sibling':
					if(in_array($widget['level'], ['Individual','Sibling'])) $to_add=true;
					break;
				case 'Individual':															
					if(in_array($widget['level'], ['Individual'])) $to_add=true;
					break;
			}

			if($to_add){
				$enum_array[$widget[0]] = $widget['title'];
			}				
				
		}

		return $enum_array;
	}
}