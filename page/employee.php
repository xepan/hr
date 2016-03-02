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

		$crud->setModel($employee,['first_name','last_name','post','created_at','status','email']);
		$f = $crud->grid->addQuickSearch(['first_name','last_name']);

		$d_f =$f->addField('DropDown','department_id')->setEmptyText("All");
		$d_f->setModel('xepan\hr\Department');
		$d_f->js('change',$f->js()->submit());

		$f->addHook('appyFilter',function($f,$m){
			if($f['department_id'])
				$m->addCondition('department_id',$f['department_id']);
		});
		
	}
}
