<?php

namespace xepan\hr;

class Model_LeaveTemplate extends \xepan\base\Model_Table{
	public $table= "leave_template";
	public $actions = ['*'=>['view','edit','delete']];
 	public $acl_type = "LeaveTemplate";
 			
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);
		$this->addField('name');

		$this->hasMany('xepan\hr\LeaveTemplateDetail','leave_template_id');
	}
}