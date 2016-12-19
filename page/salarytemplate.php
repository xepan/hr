<?php
namespace xepan\hr;

class page_salarytemplate extends \xepan\hr\page_config{
	public $title = "Salary Template";

	function init(){
		parent::init();
		
		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$temp_crud = $this->add('xepan\base\CRUD',null,'salary_template_view');
		// $temp_crud = $this->add('xepan\base\CRUD',null,'salary_template_view',['page/config/salarytemplate']);
		
		$temp_crud->setModel($salary_template_m);

		// $temp_crud->grid->addColumn('expander','Detail');
		$temp_crud->addRef('xepan\hr\SalaryTemplateDetails',['label'=>'Detail']);


		$info = $this->add('View',null,'salary_view')->setElement('h2');
		$info->setHtml('Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays} and {your_define_salary_names}');

		$salary = $this->add('xepan\hr\Model_Salary');
		$crud = $this->add('xepan\base\CRUD',null,'salary_view',['page/config/salarydetail']);
		$crud->setModel($salary);


	}

	function page_Detail(){
		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$salary_template_m->load($this->app->stickyGET('salary_template_id'));

		$salary_template_detail=$salary_template_m->ref('xepan\hr\SalaryTemplateDetails');

		$crud=$this->add('xepan\base\CRUD',null,null,['page/config/salarytemplatedetail']);
		$crud->setModel($salary_template_detail);
		// $crud->grid->addQuickSearch(['leave']);
	}

	function defaultTemplate(){
		return ['page/salarytemplate'];
	}
}