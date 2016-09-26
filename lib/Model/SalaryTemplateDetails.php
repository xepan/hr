<?php

namespace xepan\hr;

/**
* 
*/
class Model_SalaryTemplateDetails extends \xepan\base\Model_Table{
	public $table = "salary_template_details";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\SalaryTemplate','salary_template_id');
		$this->hasOne('xepan\hr\Salary','salary_id');

		$this->addField('amount')->type('int');

		$this->addExpression('unit')->set(function($m,$q){
			return $m->ref('salary_id')->fieldQuery('unit');
		});
	}
}