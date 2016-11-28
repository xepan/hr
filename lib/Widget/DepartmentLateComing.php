<?php

namespace xepan\hr;

class Widget_DepartmentLateComing extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('department');
		$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
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