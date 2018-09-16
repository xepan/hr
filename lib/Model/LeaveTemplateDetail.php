<?php

namespace xepan\hr;

/**
* 
*/
class Model_LeaveTemplateDetail extends \xepan\base\Model_Table{
	public $table ="leave_template_detail";
	public $actions = ['All'=>['view','edit','delete']];
 	// public $acl_type = "LeaveTemplateDetail";
 	public  $acl = 'xepan\hr\Model_LeaveTemplate';

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\LeaveTemplate','leave_template_id');
		$this->hasOne('xepan\hr\Leave','leave_id');
		$this->addField('is_yearly_carried_forward')->type('boolean')->defaultValue(false);
		$this->addField('type')->enum(['Paid','Unpaid']);
		$this->addField('is_unit_carried_forward')->type('boolean')->defaultValue(true);
		$this->addField('unit')->enum(['Monthly','Yearly']);
		$this->addField('allow_over_quota')->type('boolean')->defaultValue(false);
		$this->addField('no_of_leave');

	}
}
