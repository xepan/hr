<?php
namespace xepan\hr;

class page_configsalary extends \xepan\hr\page_configurationsidebar{
	public $title = "Salary Template";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$salary_tab = $tabs->addTab('Salary');


		$salary = $this->add('xepan\hr\Model_Salary');
		$salary->acl = 'xepan\hr\Model_SalaryTemplate';
		$crud = $salary_tab->add('xepan\hr\CRUD',null,null,['page/config/salarydetail']);
		$crud->setModel($salary);

		$info = $crud->grid->add('View',null,'salary_view')->setElement('h2');
		$info->setHtml('Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays} and {your_define_salary_names}');
		
		$tabs->addTabURL('xepan_hr_salarytemplate','Salary Templates');
	}
}