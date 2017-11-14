<?php

namespace xepan\hr;

/**
* 
*/
class Model_Leave extends \xepan\base\Model_Table{
	public $table="leaves";
	public $actions = ['All'=>['view','edit','delete']];
 	public $acl_type = "Leave";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
	
		$this->addField('name');
		// $this->addField('is_yearly_carried_forward')->type('boolean')->defaultValue(false);
		// $this->addField('type')->enum(['Paid','Unpaid']);
		// $this->addField('is_unit_carried_forward')->type('boolean')->defaultValue(true);
		// $this->addField('no_of_leave')->type('int');
		// $this->addField('unit')->enum(['Monthly','Weekly','Quaterly','Yearly']);
		// $this->addField('allow_over_quota')->type('boolean')->defaultValue(false);
		
		$this->hasMany('xepan\hr\LeaveTemplateDetail','leave_id');
	}
}