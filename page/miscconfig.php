<?php

namespace xepan\hr;

class page_miscconfig extends \xepan\hr\page_configurationsidebar{
	public $title="Misc Configuration";

	function init(){
		parent::init();
		
		$tabs = $this->add('Tabs');
		
		// Holiday Between Leave Configuration 
		$holiday_between_leave = $tabs->addTab('Holiday Between Leave Configuration');
		$config_model = $this->add('xepan\base\Model_ConfigJsonModel',
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

		
		// Reimbursement Configuration
		$reimbursement_config = $tabs->addTab('Reimbursement Configuration');
		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
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
	}
}