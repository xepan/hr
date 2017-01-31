<?php

namespace xepan\hr;

class page_activityreport extends \xepan\base\Page{
	public $title = "Activity Report";
	
	function init(){
		parent::init();

		$report_view = $this->add('View',null,'report_view');

		$form = $this->add('Form',null,'filter_form');
		$date_range_field = $form->addField('DateRangePicker','date_range')
								 ->setStartDate($this->app->now)
								 ->setEndDate($this->app->now)
								 ->getBackDatesSet();
		$employee_field = $form->addField('autocomplete\Basic','employee');
		$employee_field->setModel('xepan\hr\Model_Employee_Active');
		$form->addSubmit('Fetch Report')->addClass('btn btn-primary xepan-push');
			
		if($_GET['search_xepan']){
			$emp_id = $_GET['employee'];
			$start_date = $_GET['from_date'];
			$end_date = $_GET['to_date'];

			$this->app->hook('activity_report',[$report_view,$emp_id,$start_date,$end_date]);
		}	
		
		if($form->isSubmitted()){
			$from_date = $date_range_field->getStartDate();
			$to_date = $date_range_field->getEndDate();
			$report_view->js()->reload(['search_xepan'=>true,'employee'=>$form['employee'],'from_date'=>$from_date,'to_date'=>$to_date])->execute();
		}	
	}

	function defaultTemplate(){
		return ['page\activityreport'];
	}
}