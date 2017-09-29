<?php

namespace xepan\hr;

class Widget_DepartmentAverageWorkHour extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->report->enableFilterEntity('Department');

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

		$attendances->addExpression('employee_department')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Employee')
						->addCondition('id',$m->getElement('employee_id'))
						->setLimit(1)
						->fieldQuery('department_id');	
		});

		if(isset($this->report->department)){
			$attendances->addCondition('employee_department',$this->report->department);
		}else{
			$attendances->addCondition('employee_department',$this->app->employee['department_id']);
		}

		if(isset($this->report->start_date))
			$attendances->addCondition('from_date','>=',$this->report->start_date);
		
		if(isset($this->report->end_date))
			$attendances->addCondition('from_date','<',$this->app->nextDate($this->report->end_date));
		
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


