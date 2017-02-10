<?php

namespace xepan\hr;

class Widget_AvailableWorkforce extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
     	$this->chart = $this->add('xepan\base\View_Chart');		
	}

	function recursiveRender(){
		$employee = $this->add('xepan\hr\Model_Employee_Active');
		$total_employees = $employee->count()->getOne();
		
		$employee = $this->add('xepan\hr\Model_Employee_Active');
		$employee->addExpression('present_today')->set(function($m,$q){
			return $m->refSQL('Attendances')
					->addCondition('employee_id',$q->getField('id'))
					->addCondition('fdate',$this->report->end_date)->count();
		})->type('boolean');

		$employee->addCondition('present_today',true);

		$present_employees = $employee->count()->getOne();
		
		$this->chart->setData(['columns'=> [['present', (($present_employees/$total_employees)*100)]],'type'=>'gauge'])
     				->setTitle('Work Force Available As On : '.$this->report->end_date)
     				->setOption('color',['pattern'=>['#FF0000', '#F97600', '#F6C600', '#60B044'],'threshold'=>['values'=>[30, 60, 90, 100]]])
     				->openOnClick('xepan_hr_widget_todaysattendance');
		
		return parent::recursiveRender();
	}
}