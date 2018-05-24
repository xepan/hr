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

class page_employee extends \xepan\base\Page {
	public $title='Employee';

	function init(){
		parent::init();
		$this->api->stickyGET('post_id');
		$this->api->stickyGET('department_id');
		
		$employee=$this->add('xepan\hr\Model_Employee');
		$employee->add('xepan\base\Controller_TopBarStatusFilter');
		
		if($status = $this->api->stickyGET('status'))
			$employee->addCondition('status',$status);	
		
		if($_GET['post_id']){
			$employee->addCondition('post_id',$_GET['post_id']);
		}
		if($_GET['department_id']){
			// throw new \Exception($this->api->stickyGET('status'), 1);
			$employee->addCondition('department_id',$_GET['department_id']);
		}
						
		$crud=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_hr_employeedetail'],null,['view/employee/employee-grid']);
		$crud->grid->addPaginator(50);
		$crud->setModel($employee);
		$crud->add('xepan\base\Controller_MultiDelete');

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
		$crud->grid->addSno();
		
		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-employee')->univ()->frameURL('Employee Details',[$this->api->url('xepan_hr_employeedetail'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}
	}
}
