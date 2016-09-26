<?php

namespace xepan\hr;

/**
* 
*/
class Model_Leave extends xepan\base\Model_Table{
	public $table="leaves";
	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('is_yearly_carried_forward')->type('boolean')->defalutValue(false);
		$this->addField('type')->enum(['Paid','Unpaid']);
		$this->addField('is_unit_carried_forward')->type('boolean')->defalutValue(true);
		$this->addField('no_of_leave')->type('int');
		$this->addField('unit')->enum(['Monthly','Weekly','Quaterly','Yearly']);
		$this->addField('allow_over_quota')->type('boolean')->defalutValue(false);
		
		$this->hasMany('xepan\hr\LeaveTemplateDetail','leave_id');
	}
}