<?php

namespace xepan\hr;

/**
* 
*/
class Model_Salary extends \xepan\base\Model_Table{
	public $table ="salary";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('type');
		$this->addField('add_deducat')->enum(['add','dedcation']);
		$this->addField('unit')->enum(['Month','Leave'])
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_id');
		$this->hasMany('xepan\hr\Employee_Salary','salary_id');
	}
}