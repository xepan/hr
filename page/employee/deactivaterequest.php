<?php

namespace xepan\hr;

/**
* 
*/
class page_employee_deactivaterequest extends \xepan\base\Page{

	public $title = "Employee Deactivate Requests";
	function init(){
		parent::init();
		
		$emp = $this->add('xepan\hr\Model_Employee')
					->addCondition('status','DeactivateRequest');

		$crud=$this->add('xepan\hr\CRUD',['allow_add'=>false]);
		$crud->grid->addPaginator(50);
		$crud->grid->addQuickSearch(['name']);
		$crud->setModel($emp,['name','post']);
		$crud->grid->removeAttachment();

	}
}