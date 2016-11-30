<?php

namespace xepan\hr;

class page_widget_employeeperformance extends \xepan\base\Page{
	public $grid;
	function init(){
		parent::init();
	
		$department_id = $this->app->stickyGET('dept_id');
		
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$emp_j = $attendances->join('employee.contact_id','employee_id');
		$emp_j->addField('department_id');
		
		$attendances->addExpression('emp_status')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);

			return $emp->fieldQuery('status'); 
		});

		$attendances->addExpression('department_name')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Department');
			$emp->addCondition('id',$m->getElement('department_id'));
			$emp->setLimit(1);

			return $emp->fieldQuery('name'); 
		});

		$attendances->addCondition('emp_status','Active');
		$attendances->addCondition('department_id',$department_id);

		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('employee_id');
		
		$this->grid = $this->add('xepan\hr\Grid',null,null,['page\widget\employeeperformance']);
		$this->grid->setModel($attendances,['employee','avg_late','avg_extra_work']);
		$this->grid->addQuickSearch(['employee']);
		$this->grid->addPaginator(10);			
	}
}