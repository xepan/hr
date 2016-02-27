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

class page_department extends \Page {
	public $title='Department';

	function init(){
		parent::init();
		
		$department=$this->add('xepan\hr\Model_Department');

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/department/department-grid']);

		$crud->setModel($department);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
