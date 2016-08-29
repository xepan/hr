<?php

namespace xepan\hr;

class page_dashboard extends \xepan\base\Page{
	public $title = 'Dashboard';

	function init(){
		parent::init();

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->_dsql()->group('department_id');
		$employee->addExpression('count','count(*)');
		
		$employee->addExpression('post_count')->set(function($m,$q){
			$dept = $this->add('xepan\hr\Model_Department');
			$dept->addCondition('name',$m->getElement('department'));
			$dept->setLimit(1);
			return $dept->fieldQuery('posts_count');
		});

		$this->add('xepan\base\Grid',null,'grid',['view\dashboard\grid'])->setModel($employee);

		$employee_in = $this->add('xepan\hr\Model_Employee_Movement');
	}

	function defaultTemplate(){
		return ['page\dashboard\dashboard'];
	}
}