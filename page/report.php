<?php

namespace xepan\hr;

/**
* 
*/
class page_report extends \xepan\base\Page{
	public $title="Employee Report";
	function init(){
		parent::init();

		// $emp = $this->add('xepan\hr\Model_Employee');

		// $emp->addExpression('salary')->set(function($m,$q){
		// 	return $m->refSQL('EmployeeSalary')->fieldQuery('amount');
		// });

		// $c = $this->add('xepan\hr\CRUD');
		// $c->setModel($emp,['name','post','salary']);

	}
}