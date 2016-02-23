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

		$employee=$this->add('xepan\hr\Model_Employee');

		
		$crud=$this->add('xepan\base\CRUD',array('grid_class'=>'xepan\base\Grid','grid_options'=>array('defaultTemplate'=>['grid/opportunity-grid'])));

		// $crud=$this->add('xepan\base\CRUD',
		// 				[
		// 					'action_page'=>'xepan_hr_employeedetail',
		// 					'grid_options'=>[
		// 									'defaultTemplate'=>['grid/employee-grid']
		// 									]
		// 				]);

		$crud->setModel($employee);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
