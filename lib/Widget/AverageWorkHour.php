<?php

namespace xepan\hr;

class Widget_AverageWorkHour extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('department');
		$this->report->enableFilterEntity('employee');
     	$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		
		$attendances->addExpression('employee_status')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Employee')
						->addCondition('id',$m->getElement('employee_id'))
						->setLimit(1)
						->fieldQuery('status');
		});

		$attendances->addCondition('employee_status','Active');

		if(isset($this->report->employee))
			$attendances->addCondition('employee_id',$this->report->employee);

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


