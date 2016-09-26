<?php

namespace xepan\hr;

class Model_SalaryTemplate extends \xepan\base\Model_Table{
	public $table = "salary_template";

	function init(){
		parent::init();

		$this->addField('name');
		
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_template_id');
	}
}