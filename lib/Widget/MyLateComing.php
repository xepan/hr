<?php

namespace xepan\hr;

class Widget_MyLateComing extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->chart = $this->add('xepan\base\View_Chart');
		$this->report->enableFilterEntity('employee');
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		
		if(isset($this->report->employee))
			$attendances->addCondition('employee_id',$this->report->employee);
		else
			$attendances->addCondition('employee_id',$this->app->employee->id);

		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('ROUND(AVG(([0])/60),2)',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('ROUND((AVG([0])/60),2)',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('employee_id');
     	
		$this->chart->setType('bar')
		    		->setModel($attendances,'employee',['avg_late','avg_extra_work'])
		    		->rotateAxis()
		    		->setTitle('Employee Avg Late Coming & Extra Work');
		return parent::recursiveRender();
	}
}