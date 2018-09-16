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

		$info = $salary_tab->add('View')->setElement('h2');

		$crud = $salary_tab->add('xepan\hr\CRUD');
		$crud->form->add('xepan\base\Controller_FLC')
			->layout([
				'name'=>'c1~3',
				'type'=>'c2~3',
				'add_deduction'=>'c3~3',
				'unit'=>'c4~3',
				'order'=>'c5~3',
				'is_reimbursement'=>'c7~3',
				'is_deduction'=>'c8~3',
				'default_value'=>'c11~12',
			]);

		$crud->setModel($salary);
		$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {ExtraWorkingDays}, {ExtraWorkingHours}, {PaidLeavesOnHoliday}, {UnPaidLeavesOnHoliday}, {PaidDays} and {your_define_salary_names}';
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
			$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents},{ExtraWorkingDays}, {ExtraWorkingHours}, {PaidLeavesOnHoliday}, {UnPaidLeavesOnHoliday}, {PaidDays}, {Reimbursement} and {your_define_salary_names}';

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
			$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {ExtraWorkingDays}, {ExtraWorkingHours}, {PaidLeavesOnHoliday}, {UnPaidLeavesOnHoliday},{PaidDays}, {Deduction} and {your_define_salary_names}';

		if($deduction_config_model['is_deduction_affect_salary'] === "yes" && 
			$reimbursement_config_model['is_reimbursement_affect_salary'] === "yes")
				$html = 'Please Use: {TotalWorkingDays}, {PaidLeaves}, {UnPaidLeaves}, {Absents}, {ExtraWorkingDays}, {ExtraWorkingHours}, {PaidLeavesOnHoliday}, {UnPaidLeavesOnHoliday},{PaidDays}, {Reimbursement}, {Deduction} and {your_define_salary_names}';
		$info->setHtml($html);
		
		$crud->grid->addSno();
		$crud->grid->removeColumn('action');
		$crud->grid->removeAttachment();
		$crud->grid->addFormatter('default_value','Wrap');
		
		$tabs->addTabURL('xepan_hr_salarytemplate','Salary Templates');
	}
}