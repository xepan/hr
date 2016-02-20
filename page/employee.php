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

		$crud=$this->add('xepan\base\CRUD',
						array(
							'grid_options'=>array(
											'defaultTemplate'=>['grid/employee-grid']
											)
						));

		$crud->setModel($employee);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
