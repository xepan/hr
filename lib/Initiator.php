<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_hr';

	function init(){
		parent::init();
		
		$this->routePages('xepan_hr');

		$this->app->employee = $this->add('xepan\hr\Model_Employee')->loadBy('user_id',$this->app->auth->model->id);

		$m = $this->app->top_menu->addMenu('HR');
		$m->addItem('Department','xepan_hr_department');
		$m->addItem('Post','xepan_hr_post');
		$m->addItem('Employee','xepan_hr_employee');
		
	}
}
