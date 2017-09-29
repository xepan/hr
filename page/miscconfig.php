<?php

namespace xepan\hr;

class page_miscconfig extends \xepan\hr\page_configurationsidebar{
	public $title="Misc Configuration";

	function init(){
		parent::init();
		
		$tabs = $this->add('Tabs');
		
		/** 
		Holiday Between Leave Configuration
		**/ 

		$holiday_between_leave = $tabs->addTab('Holiday Between Leave Configuration');
		$config_model = $holiday_between_leave->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'treat_holiday_between_leave'=>"Line",
										],
							'config_key'=>'HR_HOLIDAY_BETWEEN_LEAVES',
							'application'=>'hr'
						]);
		$config_model->add('xepan\hr\Controller_ACL');
		$config_model->tryLoadAny();

		$form = $holiday_between_leave->add('Form');
		$field = $form->addField('Dropdown','treat_holiday_between_leave')
				->setValueList(['AsHoliday'=>'As a Holiday','AsLeave'=>'As a Leave'])
				->setEmptyText('Please select..');

		if($config_model['treat_holiday_between_leave'])
			$field->set($config_model['treat_holiday_between_leave']);

		$form->addSubmit("Save")->addClass('btn btn-primary');
		if($form->isSubmitted()){
			$config_model['treat_holiday_between_leave'] = $form['treat_holiday_between_leave'];
			$config_model->save();
			$form->js(null,$form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		}

		
		/**
		Reimbursement Configuration
		**/
		$reimbursement_config = $tabs->addTab('Reimbursement Configuration');
		$reimbursement_config_model = $reimbursement_config->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_reimbursement_affect_salary'=>"Line",
										],
							'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$reimbursement_config_model->add('xepan\hr\Controller_ACL');
		$reimbursement_config_model->tryLoadAny();

		$reibursement_form = $reimbursement_config->add('Form');
		$field = $reibursement_form->addField('Dropdown','is_reimbursement_affect_salary')
				->setValueList(['yes'=>'Yes','no'=>'No'])
				->setEmptyText('Please select..');

		if($reimbursement_config_model['is_reimbursement_affect_salary'])
			$field->set($reimbursement_config_model['is_reimbursement_affect_salary']);

		$reibursement_form->addSubmit("Save")->addClass('btn btn-primary');
		if($reibursement_form->isSubmitted()){
			$reimbursement_config_model['is_reimbursement_affect_salary'] = $reibursement_form['is_reimbursement_affect_salary'];
			$reimbursement_config_model->save();
			$reibursement_form->js(null,$reibursement_form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		}

		/**
		Deduction Configuration
		**/
		$deduction_config = $tabs->addTab('Deduction Configuration');
		$deduction_config_model = $deduction_config->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_deduction_affect_salary'=>"Line",
										],
							'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$deduction_config_model->add('xepan\hr\Controller_ACL');
		$deduction_config_model->tryLoadAny();

		$deduction_form = $deduction_config->add('Form');
		$field = $deduction_form->addField('Dropdown','is_deduction_affect_salary')
				->setValueList(['yes'=>'Yes','no'=>'No'])
				->setEmptyText('Please select..');

		if($deduction_config_model['is_deduction_affect_salary'])
			$field->set($deduction_config_model['is_deduction_affect_salary']);

		$deduction_form->addSubmit("Save")->addClass('btn btn-primary');
		if($deduction_form->isSubmitted()){
			$deduction_config_model['is_deduction_affect_salary'] = $deduction_form['is_deduction_affect_salary'];
			$deduction_config_model->save();
			$deduction_form->js(null,$deduction_form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		}

		/**
		Salary Due Entry Configuration 
		**/
		$sal_due_config = $tabs->addTab('SalaryDueEntry');
		$sal_due_entry_config_m = $sal_due_config->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_salary_due_entry_afftect_employee_ledger'=>"Line",
										],
							'config_key'=>'HR_SALARY_DUE_ENTRY_AFFECT_EMPLOYEE_LEDGER',
							'application'=>'hr'
						]);
		$sal_due_entry_config_m->add('xepan\hr\Controller_ACL');
		$sal_due_entry_config_m->tryLoadAny();

		$sal_due_entry_form = $sal_due_config->add('Form');
		$field = $sal_due_entry_form->addField('Dropdown','is_salary_due_entry_afftect_employee_ledger')
				->setValueList(['yes'=>'Yes','no'=>'No'])
				->setEmptyText('Please select..');

		if($sal_due_entry_config_m['is_salary_due_entry_afftect_employee_ledger'])
			$field->set($sal_due_entry_config_m['is_salary_due_entry_afftect_employee_ledger']);

		$sal_due_entry_form->addSubmit("Save")->addClass('btn btn-primary');
		if($sal_due_entry_form->isSubmitted()){
			$sal_due_entry_config_m['is_salary_due_entry_afftect_employee_ledger'] = $sal_due_entry_form['is_salary_due_entry_afftect_employee_ledger'];
			$sal_due_entry_config_m->save();
			$sal_due_entry_form->js(null,$sal_due_entry_form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		}
	}
}