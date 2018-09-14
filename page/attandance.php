<?php

namespace xepan\hr;

class page_attandance extends \xepan\base\Page{
	public $title ="Employee Attendance";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$tabs->addTabURL('xepan_hr_employeeattandance','Employee Attendance');
		$tabs->addTabURL('xepan_hr_importattandance','Import Employee Attendance');
		$tabs->addTabURL('xepan_hr_employeeattandancereport','Employee Attendance Register');

	}
}