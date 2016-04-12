<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_hr';

	function init(){
		parent::init();
		
		$this->routePages('xepan_hr');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
		->setBaseURL('../vendor/xepan/hr/');

		if($this->app->auth->isLoggedIn())
			$this->app->employee = $this->add('xepan\hr\Model_Employee')->loadBy('user_id',$this->app->auth->model->id);

		if($this->app->is_admin){

			$m = $this->app->top_menu->addMenu('HR');
			$m->addItem(['Department','icon'=>'fa fa-sliders'],'xepan_hr_department');
			$m->addItem(['Post','icon'=>'fa fa-sitemap'],'xepan_hr_post');
			$m->addItem(['Employee','icon'=>'fa fa-user'],'xepan_hr_employee');
			$m->addItem(['Employee Movement','icon'=>'fa fa-edit'],'xepan_hr_employeemovement');
			$m->addItem(['User','icon'=>'fa fa-male'],'xepan_hr_user');
			$m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
			
			$this->app->layout->template->trySet('department',$this->app->employee['department']);
			$post=$this->app->employee->ref('post_id');
	        $this->app->layout->template->trySet('post',$post['name']);
	        $this->app->layout->template->trySet('first_name',$this->app->employee['first_name']);
	        $this->app->layout->template->trySet('status',$this->app->employee['status']);
	        
	        // $this->app->layout->add('xepan\hr\View_Notification',null,'notification_view');
	        // $this->app->layout->add('xepan\base\View_Message',null,'message_view');

		}
		$this->app->auth->addHook('loggedIn',function(){
			throw new \Exception("Error Processing Request", 1);
			
		});
	}
}
