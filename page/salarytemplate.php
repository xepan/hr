<?php
namespace xepan\hr;

class page_salarytemplate extends \xepan\hr\page_configurationsidebar{
	public $title = "Salary Template";

	function init(){
		parent::init();
		
		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($salary_template_m);
	}
}