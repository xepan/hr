<?php
namespace xepan\hr;

class page_salarytemplate extends \xepan\hr\page_config{
	public $title = "Salary Template";

	function init(){
		parent::init();
		
		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$temp_crud = $this->add('xepan\base\CRUD',null,'salary_template_view');
		$temp_crud->setModel($salary_template_m);

		$temp_crud->addRef('xepan\hr\SalaryTemplateDetails',['label'=>'Detail']);


		$salary = $this->add('xepan\hr\Model_Salary');
		$crud = $this->add('xepan\base\CRUD',null,'salary_view');
		$crud->setModel($salary);


	}

	function defaultTemplate(){
		return ['page/salarytemplate'];
	}
}