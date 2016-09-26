<?php

namespace xepan\hr;

/**
* 
*/
class Model_LeaveTemplateDetail extends \xepan\base\Model_Table{
	public $table ="leave_template_detail";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Model\LeaveTemplate','leave_template_id');
		$this->hasOne('xepan\hr\Model\Leave','leave_id');
		$this->addField('is_yearly_carried_forward')->type('boolean')->defaultValue(false);
		$this->addField('type')->enum(['Paid','Unpaid']);
		$this->addField('is_unit_carried_forward')->type('boolean')->defaultValue(true);
		$this->addField('unit')->enum(['Monthly','Weekly','Quaterly','Yearly']);
		$this->addField('allow_over_quota')->type('boolean')->defaultValue(false);
		$this->addField('no_of_leave');

	}
}
