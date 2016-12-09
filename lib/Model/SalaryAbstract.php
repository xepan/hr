<?php

namespace xepan\hr;

class Model_SalaryAbstract extends \xepan\base\Model_Table{
	public $table ="salary_abstract";

	function init(){
		parent::init();

		$this->hasOne('xepan\base\Contact','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->hasOne('xepan\base\Contact','updated_by_id')->defaultValue($this->app->employee->id)->system(true);

		$this->addField('created_at')->type('date')->defaultValue($this->app->now)->sortable(true);
		$this->addField('updated_at')->type('date')->defaultValue($this->app->now)->sortable(true);
		
		$this->addField('name');
		$this->addField('month')->enum(['1','2','3','4','5','6','7','8','9','10','11','12']);
		$year = ['2015','2016','2017','2018'];
		$this->addField('year')->enum($year);

		$this->addField('type')->setValueList(['SalarySheet'=>'Salary Sheet','SalaryPayment'=>'Salary Payment'])->mandatory(true);

		$this->hasMany('xepan\hr\EmployeeRow','salary_abstract_id');

		$this->is(['name|required','month|required','year|required']);
	}
}