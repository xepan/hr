<?php

namespace xepan\hr;

class page_aclconfig extends \xepan\hr\page_configurationsidebar{
	public $title = "ACL Configuration";

	function init(){
		parent::init();
		if($this->app->employee['scope'] !== 'SuperUser'){
			$this->add('H1')->set('You are not Authorized User');
			return ;
		}
		$acl_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'access_level'=>'DropDown'
							],
					'config_key'=>'ACLMode',
					'application'=>'hr'
			]);
		$acl_m->tryLoadAny();

		$form = $this->add('Form_Stacked');
		$form->setModel($acl_m);

		$acl_mode_field=$form->getElement('access_level')->set($acl_m['access_level']);
		$acl_mode_field->setValueList(['none'=>'Allow','Departmental'=>'Departmental','Documentbase'=>'Documentbase']);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$acl_m->app->employee
			    ->addActivity("'ACL Mode' Updated as '".$form['access_level']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_hr_page_aclconfig")
				->notifyWhoCan(' ',' ',$acl_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}
}