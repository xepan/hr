<?php

namespace xepan\hr;

class page_miscconfig extends \xepan\hr\page_config{
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
		$config_model->tryLoadAny();

		$form = $this->add('Form');
		$field = $form->addField('Dropdown','treat_holiday_between_leave')
				->setValueList(['AsHoliday'=>'As a Holiday','AsLeave'=>'As a Leave']);

		if($config_model['treat_holiday_between_leave'])
			$field->set($config_model['treat_holiday_between_leave']);

		$form->addSubmit("Save");
		if($form->isSubmitted()){
			$config_model['treat_holiday_between_leave'] = $form['treat_holiday_between_leave'];
			$config_model->save();
			$form->js(null,$form->js()->univ()->successMessage('Saved'))->reload()->execute();
		}

	}
}