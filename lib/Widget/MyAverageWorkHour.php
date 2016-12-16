<?php

namespace xepan\hr;

class Widget_MyAverageWorkHour extends \xepan\base\Widget{
	function init(){
		parent::init();

     	$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendances->addCondition('employee_id',$this->app->employee->id);
		$attendances->addExpression('avg_work_hours')->set($attendances->dsql()->expr('AVG([0])',[$attendances->getElement('working_hours')]));
		$attendances->_dsql()->group('employee_id');
     	
	    $this->chart->setType('bar')
	    			->setModel($attendances,'employee',['avg_work_hours'])
	    			->rotateAxis()
	    			->setTitle('Employee Avg Work Hour')
	    			->openOnClick('xepan_hr_widget_averageworkhour');
		
		return parent::recursiveRender();
	}
}