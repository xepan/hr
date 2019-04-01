<?php

namespace xepan\hr;
class Model_Config_MuteNotificationForEmployee extends \xepan\base\Model_ConfigJsonModel{
	public $fields =[
				'employee_id'=>"DropDown",
				'employee'=>"Line",
			];
	public $config_key = 'Mute_Notification_For_Employee';
	public $application = 'xepan\hr';

	function init(){
		parent::init();

		$this->getElement('employee_id')->setModel('xepan\hr\Model_Employee');
		$this->addHook('afterLoad',function($m){
			$m['employee'] = $this->add('xepan\hr\Model_Employee')->load($m['employee_id'])->get('name');
		});

	}
}