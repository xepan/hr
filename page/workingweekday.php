<?php

namespace xepan\hr;

class page_workingweekday extends \xepan\hr\page_configurationsidebar{
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
		$form->addSubmit("Save")->addClass("btn btn-primary");
		if($form->isSubmitted()){			
			$week_day_model['monday'] = $form['monday'];
			$week_day_model['tuesday'] = $form['tuesday'];
			$week_day_model['wednesday'] = $form['wednesday'];
			$week_day_model['thursday'] = $form['thursday'];
			$week_day_model['friday'] = $form['friday'];
			$week_day_model['saturday'] = $form['saturday'];
			$week_day_model['sunday'] = $form['sunday'];
			$week_day_model->save();
			$form->js(null,$form->js()->univ()->successMessage('Working Week Days Updated'))->reload()->execute();
		}



		$ip_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'ip'=>"line"
										],
							'config_key'=>'HR_ALLOWED_IP_4_ATTENDANCE',
							'application'=>'hr'
						]);
		$ip_model->getElement('ip')->caption('Allowed attendance from IPs');
		$ip_model->tryLoadAny();

		$form = $this->add('Form');
		$form->setModel($ip_model);
		$form->addSubmit("Save")->addClass("btn btn-primary");
		if($form->isSubmitted()){
			if(strpos($form['ip'], ' ')!==false){
				$form->displayError('ip','Spaces are not allowed');
			}

			$ip_model['ip'] = preg_replace('/\s+/', '', $form['ip']);
			$ip_model->save();
			$form->js(null,$form->js()->univ()->successMessage('IP Allowed for Attendance updated'))->reload()->execute();
		}

	}
}