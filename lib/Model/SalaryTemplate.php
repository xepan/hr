<?php

namespace xepan\hr;

class Model_SalaryTemplate extends \xepan\base\Model_Table{
	public $table = "salary_template";
	public $actions = ['*'=>['view','edit','delete']];
 	public $acl_type = "SalaryTemplate";

	function init(){
		parent::init();

		$this->addField('name');
		
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_template_id');
	}
}