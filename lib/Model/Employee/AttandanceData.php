<?php

namespace xepan\hr;

class Model_Employee_AttandanceData extends \xepan\hr\Model_Employee{

	public $curr_date;

	function init(){
		parent::init();

		if(!$this->curr_date) $this->curr_date = $this->app->today;

		$this->year = $this->app->getYear($this->curr_date);
		$this->month = $this->app->getMonth($this->curr_date);
		$this->day = $this->app->getDay($this->curr_date);
		$this->days_count = $this->app->getDaysInMonth($this->curr_date);
		$this->first_date = date('Y-m-01', strtotime($this->curr_date));
		$this->last_date = date('Y-m-t', strtotime($this->curr_date));

		for($i=1; $i <= $this->days_count ; $i++){
			$this->addExpression("attendance_of_".$i)->set(function($m,$q)use($i){
				$et = $m->add('xepan\hr\Model_Employee_Attandance');
				$et->addCondition('employee_id',$m->getElement('id'));
				$et->addCondition('fdate',$this->year."-".$this->month."-".$i);
				return $et->count();
			})->caption($i);

			$this->addExpression("in_time_of_".$i)->set(function($m,$q)use($i){
				$et = $m->add('xepan\hr\Model_Employee_Attandance');
				$et->addCondition('employee_id',$m->getElement('id'));
				$et->addCondition('fdate',$this->year."-".$this->month."-".$i);
				$et->setLimit(1);
				return $q->expr('TIME([0])',[$et->fieldQuery('from_date')]);
			});

			$this->addExpression("out_time_of_".$i)->set(function($m,$q)use($i){
				$et = $m->add('xepan\hr\Model_Employee_Attandance');
				$et->addCondition('employee_id',$m->getElement('id'));
				$et->addCondition('fdate',$this->year."-".$this->month."-".$i);
				$et->setLimit(1);
				return $q->expr('TIME([0])',[$et->fieldQuery('to_date')]);
			});
		}

		$this->addExpression("total_attendance")->set(function($m,$q){
				$et = $m->add('xepan\hr\Model_Employee_Attandance');
				$et->addCondition('employee_id',$m->getElement('id'));
				$et->addCondition('fdate','>=',$this->first_date);
				$et->addCondition('fdate','<=',$this->last_date);
				return $et->count();
			});

	}
}