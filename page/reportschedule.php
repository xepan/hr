<?php
namespace xepan\hr;

class page_reportschedule extends \xepan\hr\page_configurationsidebar{
	public $title = "Report Schedule";

	function init(){
		parent::init();

		$report_executor = $this->add('xepan\hr\Model_ReportExecutor');

		$crud = $this->add('xepan\hr\CRUD',null,null,['page\reportexecutor']);
		
		if($crud->isEditing()){
			$crud->form->setLayout('form\reportexecutor');
		}

		$crud->setModel($report_executor,['employee','post','department','widget','time_span','date','day','annual_starting','starting_from_date','data_range','month_end','financial_month_start','post_at'],['schedule_time','time_span','employee','department','post','status']);		
		
		if($crud->isEditing()){
			$this->bindConditionalShow($crud->form);

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
			$widget_field->setModel('xepan\base\Model_GraphicalReport_Widget');
		}

		$crud->grid->addHook('formatRow',function($g,$m){
			$g->current_row_template['schedule_time'] = 'Daily';
		});
	}

	function bindConditionalShow($form){
		$time_span_field = $form->getElement('time_span');
		$time_span_field->js(true)->univ()->bindConditionalShow([
				'Weekely'=>['day'],
				'Fortnight'=>['data_range','starting_from_date'],
				'Monthly'=>['data_range','post_at','starting_from_date'],
				'Quarterly'=>['data_range','post_at','starting_from_date','financial_month_start'],
				'Halferly'=>['data_range','post_at','starting_from_date','financial_month_start'],
				'Yearly'=>['data_range','post_at','starting_from_date','financial_month_start']
			],'div.atk-form-row');

		$post_at_field = $form->getElement('post_at');
		$post_at_field->js(true)->univ()->bindConditionalShow([
				'MonthEnd'=>['month_end']
			],'div.atk-form-row');
	}
}