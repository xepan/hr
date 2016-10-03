<?php

namespace xepan\hr;

class page_employeeattandance extends \xepan\base\Page{
	public $title ="Employee Attandance";

	function init(){
		parent::init();
		$attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
		$c = $this->add('xepan\hr\CRUD');
		$c->setModel($attan_m);

	}
}