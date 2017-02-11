<?php

namespace xepan\hr;

class page_employeeattandancereport extends \xepan\base\Page{
	public $title ="Employee Attandance Report";

	function init(){
		parent::init();

		$emp_id= $this->api->stickyGET('employee_id');

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');


		$form=$this->add('Form',null,null,['form/empty']);
		
		$grid = $this->add('Grid');
		$grid->setModel($employee,['name']);

		$view = $this->add('View');
		$view->set($grid);

		$emp = $form->addField('DropDown','employee');
		$emp->setModel($employee);

		$last_seven_days = date("Y-m-d H:i:s", strtotime('- 1 Weeks', strtotime($this->app->now)));
		$date_range = $form->addField('DateRangePicker','date_range')
						->setStartDate($last_seven_days)
						->setEndDate($this->app->now);

		$form->addSubmit('Show Attandance')->addClass('btn btn-info');

		if($form->isSubmitted()){
			return $form->js(null,$view->js()->reload([
													'employee_id'=>$form['employee']
													]
													))->execute();
		}
	}
}