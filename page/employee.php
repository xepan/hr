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
<<<<<<< HEAD

		
		$crud=$this->add('xepan\base\CRUD',array('grid_class'=>'xepan\base\Grid','grid_options'=>array('defaultTemplate'=>['grid/opportunity-grid'])));

		// $crud=$this->add('xepan\base\CRUD',
		// 				[
		// 					'action_page'=>'xepan_hr_employeedetail',
		// 					'grid_options'=>[
		// 									'defaultTemplate'=>['grid/employee-grid']
		// 									]
		// 				]);
=======
		
		if($_GET['post_id']){
			$employee->addCondition('post_id',$_GET['post_id']);
		}
		
		$crud=$this->add('xepan\hr\CRUD',
						[
							'action_page'=>'xepan_hr_employeedetail',
							'grid_options'=>[
											'defaultTemplate'=>['grid/employee-grid']
											]
						]);
>>>>>>> e8b4f7329e7ff0ec3b1b8b51caf66a4935438719

		$crud->setModel($employee,['first_name','last_name','post','created_at']);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
