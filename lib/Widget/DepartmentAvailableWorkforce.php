<?php

namespace xepan\hr;

class Widget_DepartmentAvailableWorkforce extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('department');
     	$this->chart = $this->add('xepan\base\View_Chart');		
	}

	function recursiveRender(){
		$employee = $this->add('xepan\hr\Model_Employee_Active');
		
		if(isset($this->report->department)){
			$employee->addCondition('department_id',$this->report->department);						
		}
		else{			
			$employee->addCondition('department_id',$this->app->employee['department_id']);
		}

		$total_employees = $employee->count()->getOne();
		
		$employee->addExpression('present_today')->set(function($m,$q){
			return $m->refSQL('Attendances')
					->addCondition('employee_id',$q->getField('id'))
					->addCondition('from_date','>=',$this->app->today)->count();
		})->type('boolean');

		$employee->addCondition('present_today',true);
		$present_employees = $employee->count()->getOne();
		
		if($total_employees >0)
			$total = ($present_employees/$total_employees)*100;
		else
			$total = 0;

		$this->chart->setData(['columns'=> [['present', $total]],'type'=>'gauge'])
     				->setTitle('Department Work Force Available')
     				->setOption('color',['pattern'=>['#FF0000', '#F97600', '#F6C600', '#60B044'],'threshold'=>['values'=>[30, 60, 90, 100]]]);
		
		return parent::recursiveRender();
	}
}


