<?php

namespace xepan\hr;

/**
* 
*/
class Model_SalaryTemplateDetails extends \xepan\base\Model_Table{
	public $table = "salary_template_details";
	public $actions = ['*'=>['view','edit','delete']];
 	public $acl_type = "SalaryTemplateDetails";

	function init(){
		parent::init();
		$this->hasOne('xepan\hr\SalaryTemplate','salary_template_id');
		$this->hasOne('xepan\hr\Salary','salary_id');

		$this->addField('amount')->hint('leave empty to get default salary value');

		$this->addExpression('unit')->set(function($m,$q){
			return $m->refSQL('salary_id')->fieldQuery('unit');
		});

		$this->addExpression('salary_order')->set($this->refSQL('salary_id')->fieldQuery('order'));
		$this->setOrder('salary_order','asc');		
		$this->addHook('beforeSave',$this);
		$this->is(['salary_id|required']);
	}

	function beforeSave(){
		if(!$this['amount']){
			$salary= $this->add("xepan\hr\Model_Salary")->tryLoad($this['salary_id']);
			if($salary->loaded()){
				$this['amount'] = $salary['default_value'];
			}
		}
	}

}