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
		
		$crud=$this->add('xepan\base\CRUD',
						[
							'action_page'=>'xepan_hr_employeedetail',
							'grid_options'=>[
											'defaultTemplate'=>['grid/employee-grid']
											]
						]);

		$crud->setModel($employee);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
