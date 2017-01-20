<?php

namespace xepan\hr;

class page_miscconfig extends \xepan\hr\page_configurationsidebar{
	public $title="Misc Configuration";

	function init(){
		parent::init();
		
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

		$form = $this->add('Form');
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

	}
}