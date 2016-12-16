<?php

namespace xepan\hr;

class Widget_LateComing extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('employee_id');
     	
		$this->chart->setType('bar')
		    		->setModel($attendances,'employee',['avg_late','avg_extra_work'])
		    		->rotateAxis()
		    		->setTitle('Employee Avg Late Coming & Extra Work');
		
		return parent::recursiveRender();
	}
}