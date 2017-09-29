<?php

namespace xepan\hr;

class Model_OfficialHoliday extends \xepan\base\Model_Table{
	public $table ="official_holiday";
	
	public $status=['All'];
	public $acl_type = "OfficialHoliday";
	public $actions = [
		'All'=>['view','edit','delete']
	];
	public $month;
	public $year;

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->addField('name')->caption("holiday");
		$this->addField('from_date')->type('date');
		$this->addField('to_date')->type('date');
		$this->addField('type')->setValueList(['official'=>'Official','government'=>"Government",'national'=>"National",'international'=>"International",'other'=>"Other"]);
		$this->addField('status')->set('All')->system(true);
		$this->addExpression('month')->set('MONTH(from_date)');
		$this->addExpression('year')->set('YEAR(from_date)');
		$this->addExpression('month_holidays')
				->set('DATEDIFF(to_date,from_date) + 1')
				->caption('holiday day\'s');

		if(!$this->month) $this->month = date('m',strtotime($this->app->monthFirstDate()));
		if(!$this->year) $this->year = date('Y', strtotime($this->app->monthFirstDate()));

		$this->addExpression('month_from_date')->set(function($m,$q){
			$month_start_date = date($this->year.'-'.$this->month.'-01');
			return $q->expr("IF(([from_date] > '[month_start_date]'),[from_date],'[month_start_date]')",
														[
															'from_date'=>$m->getElement('from_date'),
															'month_start_date'=>$month_start_date
														]);
		})->type('date');

		$this->addExpression('month_to_date')->set(function($m,$q){
			$month_to_date =  date('Y-m-t',strtotime($this->year.'-'.$this->month.'-01'));
			return $q->expr("IF(([to_date] < '[month_to_date]'),[to_date],'[month_to_date]')",
														[
															'to_date'=>$m->getElement('to_date'),
															'month_to_date'=>$month_to_date
														]);
		})->type('date');

	}
}