<?php
namespace xepan\hr;

class page_reportexecutor extends \xepan\hr\page_configurationsidebar{
	public $title = "Report Executor";

	function init(){
		parent::init();

		$report_executor = $this->add('xepan\hr\Model_ReportExecutor');
		$report_executor->addExpression('total_count')->set(function($m,$q){
			return "'0'";
			// employee_count
			$emp  = $m->add('xepan\hr\Model_Employee',['table_alias'=>'xe'])->_dsql()
					->where($q->expr('find_in_set(xe.id,[0])',[$m->getElement('employee')]));
			$emp_count = $emp->count();	
			// post employee_count
			$post_emp  = $m->add('xepan\hr\Model_Employee',['table_alias'=>'xep'])->_dsql()
				->where($q->expr('find_in_set(post_id,[0])',[$m->getElement('post')]))
				->where($q->expr('(xep.id not in [0])',[$emp->fieldQuery('id')]));
			$post_emp_count = $post_emp->count();
			// department_employee_count
			$department_emp_count  = $m->add('xepan\hr\Model_Employee',['table_alias'=>'xed'])->_dsql()
						->where($q->expr('find_in_set(department_id,[0])',[$m->getElement('department')]))
						->where($q->expr('(xed.id not in [0])',[$emp->fieldQuery('id')]))
						->where($q->expr('(xed.id not in [0])',[$post_emp->fieldQuery('id')]))
						;
			
			return $q->expr('(IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0))',[
					$emp_count,
					$post_emp_count,
					$department_emp_count
				]);

		});

		$crud = $this->add('xepan\hr\CRUD',null,null,['page\reportexecutor']);
		
		if($crud->isEditing()){
			$crud->form->setLayout('form\reportexecutor');
		}

		$crud->setModel($report_executor,['employee','post','department','widget','time_span','financial_month_start','starting_from_date','data_range'],['time_span','total_count','status']);		
		
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
}