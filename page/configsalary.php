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
		$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays} and {your_define_salary_names}';
		
		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_reimbursement_affect_salary'=>"Line",
						],
			'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$reimbursement_config_model->tryLoadAny();

		if($reimbursement_config_model['is_reimbursement_affect_salary'] === "yes")
			$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays}, {Reimbursement} and {your_define_salary_names}';

		$deduction_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_deduction_affect_salary'=>"Line",
						],
			'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$deduction_config_model->tryLoadAny();

		if($deduction_config_model['is_deduction_affect_salary'] === "yes")
			$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays}, {Deduction} and {your_define_salary_names}';

		if($deduction_config_model['is_deduction_affect_salary'] === "yes" && 
			$reimbursement_config_model['is_reimbursement_affect_salary'] === "yes")
				$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {PaidDays}, {Reimbursement}, {Deduction} and {your_define_salary_names}';
		
		$info->setHtml($html);
		
		$tabs->addTabURL('xepan_hr_salarytemplate','Salary Templates');
	}
}