<?php

namespace xepan\hr;

/**
* 
*/
class page_report_employeeattandance extends \xepan\base\Page{

	public $title = "Employee Attandance Report";
	function init(){
		parent::init();

		$emp_id = $this->app->stickyGET('employee_id');
		// $from_date = $this->app->stickyGET('from_date');
		// $to_date = $this->app->stickyGET('to_date');
		$form = $this->add('Form',null,null,['form/empty']);
		// $date = $form->addField('DateRangePicker','date_range');
		// $set_date = $this->app->today." to ".$this->app->today;
		// if($from_date){
		// 	$set_date = $from_date." to ".$to_date;
		// 	$date->set($set_date);	
		// }
		$emp_field = $form->addField('xepan\base\Basic','employee');
		$emp_field->setModel('xepan\hr\Model_Employee')->addCondition('status','Active');
		
		$emp_model = $this->add('xepan\hr\Model_Employee'/*,['from_date'=>$from_date,'to_date'=>$to_date]*/);
		if($emp_id){
			$emp_model->addCondition('id',$emp_id);
		}


		// if($_GET['from_date']){
		// 	$emp_model->from_date = $_GET['from_date'];
		// }
		$form->addSubmit('Get Details')->addClass('btn btn-primary');
		$grid = $this->add('xepan\hr\Grid',null,null,['view/report/emp-attandance-grid-view']);
		
		// $emp_model->addExpression('from_time')->set(function($m,$q){
		// 	$attandance $m->add('xepan\hr\Model_Employee_Attandance');
		// 	$attandance->dsql()->expr('[]')
		// });


		$emp_model->addExpression('in_time_login')->set(function($m,$q){
			$att = $this->add('xepan\hr\Model_Employee_Attandance');
			return $att->addCondition('employee_id',$q->getField('id'))
				->addCondition('from_time','<=',$m->getElement('in_time'))
				->count();
		});

		$emp_model->addExpression('after_time_login')->set(function($m,$q){
			$att = $this->add('xepan\hr\Model_Employee_Attandance');
			return $att->addCondition('employee_id',$q->getField('id'))
				->addCondition('from_time','>',$m->getElement('in_time'))
				->count();
		});
		$emp_model->addExpression('average_late_hour')->set(function($m,$q){ 
			return '"123"';
			$attendances = $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'avg_emp_hours']);
			return $attendances->dsql()
								->expr('CONCAT(ROUND((AVG([0])/60),2)," Hours")',
										[
											$attendances->getElement('late_coming')
										]
									);
		});
		$emp_model->addExpression('total_working_hour')->set(function($m,$q){ 
			$attendances = $m->add('xepan\hr\Model_Employee_Attandance');
			$attendances->addCondition('employee_id',$q->getField('id'));
			return $attendances->_dsql()->expr('AVG([0])',
									[
										$attendances->getElement('working_hours')
									]);
		});

		$grid->setModel($emp_model);
	}	
}