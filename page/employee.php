<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_employee extends \Page {
	public $title='Employee';

	function init(){
		parent::init();
		$this->api->stickyGET('post_id');

		$employee=$this->add('xepan\hr\Model_Employee');
		
		if($_GET['post_id']){
			$employee->addCondition('post_id',$_GET['post_id']);
		}
						
		$crud=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_hr_employeedetail'],null,['view/employee/employee-grid']);

		$crud->setModel($employee);
		$f = $crud->grid->addQuickSearch(['first_name','last_name']);

		$d_f =$f->addField('DropDown','department_id')->setEmptyText("All Department");
		$d_f->setModel('xepan\hr\Department');
		$d_f->js('change',$f->js()->submit());
		
		$p_f =$f->addField('DropDown','post_id')->setEmptyText("All Post");
		$p_f->setModel('xepan\hr\Post');
		$p_f->js('change',$f->js()->submit());

		$u_f=$f->addField('DropDown','user_id')->setEmptyText('All User');
		$u_f->setModel('xepan\base\User');
		$u_f->js('change',$f->js()->submit());

		$s_f=$f->addField('DropDown','status')->setValueList(['Active'=>'Active','Inactive'=>'Inactive'])->setEmptyText('All Status');
		$s_f->js('change',$f->js()->submit());

		$f->addHook('appyFilter',function($f,$m){
			if($f['department_id'])
				$m->addCondition('department_id',$f['department_id']);
			if($f['post_id'])
				$m->addCondition('user_id',$f['user_id']);
			if($f['user_id'])
				$m->addCondition('post_id',$f['post_id']);
			
			if($f['status']='Active'){
				$m->addCondition('status','Active');
			}else{
				$m->addCondition('status','Inactive');

			}

		});

		$crud->add('xepan\base\Controller_Avatar');
		
	}
}
