<?php

namespace xepan\hr;

class page_workingweekday extends \xepan\hr\page_config{
	public $title="Working Week Day";
	function init(){
		parent::init();

		$week_day_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'monday'=>"checkbox",
										'tuesday'=>"checkbox",
										'wednesday'=>"checkbox",
										'thursday'=>"checkbox",
										'friday'=>"checkbox",
										'saturday'=>"checkbox",
										'sunday'=>"checkbox"
										],
							'config_key'=>'HR_WORKING_WEEK_DAY',
							'application'=>'hr'
						]);
		$week_day_model->tryLoadAny();

		$form = $this->add('Form');
		$form->setModel($week_day_model);
		$form->addSubmit("Save");
		if($form->isSubmitted()){			
			$week_day_model['monday'] = $form['monday'];
			$week_day_model['tuesday'] = $form['tuesday'];
			$week_day_model['wednesday'] = $form['wednesday'];
			$week_day_model['thursday'] = $form['thursday'];
			$week_day_model['friday'] = $form['friday'];
			$week_day_model['saturday'] = $form['saturday'];
			$week_day_model['sunday'] = $form['sunday'];
			$week_day_model->save();
			$form->js(null,$form->js()->univ()->successMessage('Saved'))->reload()->execute();
		}

	}
}