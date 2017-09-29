<?php

namespace xepan\hr;

class Model_SalaryTemplate extends \xepan\base\Model_Table{
	public $table = "salary_template";
	public $actions = ['All'=>['view','edit','delete']];
 	public $acl_type = "SalaryTemplate";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->addField('name');
		
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_template_id');
	}
}