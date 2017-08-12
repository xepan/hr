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
		
		$attandance = $this->add('xepan\hr\Model_Employee_Attandance'/*,['from_date'=>$from_date,'to_date'=>$to_date]*/);
		$attandance->addExpression('employee_status')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Employee')
						->addCondition('id',$m->getElement('employee_id'))
						->setLimit(1)
						->fieldQuery('status');
		});
		$attandance->addCondition('employee_status','Active');
		if($emp_id){
			$attandance->addCondition('id',$emp_id);
		}


		// if($_GET['from_date']){
		// 	$attandance->from_date = $_GET['from_date'];
		// }
		$form->addSubmit('Get Details')->addClass('btn btn-primary');
		$grid = $this->add('xepan\hr\Grid',null,null,['view/report/emp-attandance-grid-view']);
		


		// $attandance->addExpression('in_time_login')->set(function($m,$q){
		// 	return $m->add('xepan\hr\Model_Employee')
		// 				->addCondition('in_time', '>',$m->getElement('from_time'))->sum();

		// });

		// $attandance->addExpression('after_time_login')->set(function($m,$q){
		// 	$att = $this->add('xepan\hr\Model_Employee_Attandance');
		// 	return $att->addCondition('employee_id',$q->getField('employee_id'))
		// 		->addCondition('from_time','>',$m->refSQL('employee_id')->fieldQuery('in_time'))
		// 		->count();
		// });

		// $attandance->addExpression('average_late_hour')->set($attandance->dsql()->expr('CONCAT(ROUND((AVG([0])/60),2)," Hours")',[$attandance->getElement('late_coming')]));
		// $attandance->addExpression('total_working_hour')->set($attandance->dsql()->expr('AVG([0])',[$attandance->getElement('working_hours')]));
  
		// $attandance->addExpression('total_average_late_hour')->set($attandance->dsql()->expr('SUM([0])',[$attandance->getElement('average_late_hour')]));
		// $attandance->addExpression('total_avg_working_hour')->set($attandance->dsql()->expr('SUM([0])',[$attandance->getElement('avg_working_hour')]));


		$attandance->_dsql()->group('employee_id');
		$grid->setModel($attandance);
	}	
}