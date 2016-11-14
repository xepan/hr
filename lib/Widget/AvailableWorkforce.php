<?php

namespace xepan\hr;

class Widget_AvailableWorkforce extends \xepan\base\Widget{
	function init(){
		parent::init();

     	$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		$employee = $this->add('xepan\hr\Model_Employee_Active');
		$total_employees = $employee->count()->getOne();
		
		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addExpression('present_today')->set(function($m,$q){
			return $m->refSQL('Attendances')
					->addCondition('employee_id',$q->getField('id'))
					->addCondition('from_date','>=',$this->app->today)->count();
		})->type('boolean');

		$employee->addCondition('present_today',true);

		$present_employees = $employee->count()->getOne();

		$this->chart->setData(['columns'=> [['present', ($present_employees/$total_employees*100)]],'type'=>'gauge'])
     				->setTitle('Work Force Available')
     				->setOption('color',['pattern'=>['#FF0000', '#F97600', '#F6C600', '#60B044'],'threshold'=>['values'=>[30, 60, 90, 100]]]);
		
		return parent::recursiveRender();
	}
}


