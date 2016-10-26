<?php

namespace xepan\hr;

class Model_OfficialHoliday extends \xepan\base\Model_Table{
	public $table ="official_holiday";
	
	public $status=['all'];
	public $acl_type = "OfficialHoliday";
	public $actions = [
		'all'=>['edit','delete']
	];

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);
		$this->addField('name');
		$this->addField('from_date')->type('date');
		$this->addField('to_date')->type('date');
		$this->addField('type')->setValueList(['official'=>'Official','government'=>"Government",'national'=>"National",'international'=>"International",'other'=>"Other"]);
	}
}