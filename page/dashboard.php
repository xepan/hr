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

		$employee_movement = $this->add('xepan\hr\Model_Employee');

		$count = 0;
		foreach ($employee_movement as $emp){
			$mov = $this->add('xepan\hr\Model_Employee_Movement');
			$mov->addCondition('employee_id',$emp->id);
			$mov->addCondition('movement_at','>=',$this->app->today);
			$mov->setOrder('movement_at','desc');
			$mov->tryLoadAny();

			if($mov['direction'] == 'Out' || !$mov->loaded())
				$count ++;
		}

		$this->template->trySet('out_employees',$count);

		$total_employee = $employee_movement->count()->getOne();
		$this->template->trySet('in_employees',abs($count-$total_employee));
	}

	function defaultTemplate(){
		return ['page\dashboard\dashboard'];
	}
}