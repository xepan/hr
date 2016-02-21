<?php

namespace xepan\hr;

class Model_Post extends \xepan\base\Model_Table{
	public $table="post";
	
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan');

		$this->hasOne('xepan\hr\Department');
		$this->addField('name');

		$this->hasMany('xepan\hr\Employee',null,null,'Employees');

		$this->addExpression('employee')->set(function($m,$q){
			return 5;
		});
	}
}
