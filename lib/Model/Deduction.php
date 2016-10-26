<?php

namespace xepan\hr;

class Model_Deduction extends \xepan\base\Model_Table{
	public $table = "deduction";
	
	public $status=['all'];
	public $acl_type = "Deduction";
	public $actions = [
		'all'=>['edit','delete']
	];

	function init(){
		parent::init();

		$this->hasOne('xepan/hr/Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->hasOne('xepan/hr/Employee','employee_id')->sortable(true);
		$this->addField('name')->caption('Reason');
		$this->addField('amount')->type('money');
		$this->addField('created_at')->type('date')->defaultValue($this->app->now)->sortable(true)->system(true);
		
		$this->addField('narration')->type('text');
	}
}