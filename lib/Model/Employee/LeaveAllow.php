<?php


namespace xepan\hr;

/**
* 
*/
class Model_Employee_LeaveAllow extends \xepan\base\Model_Table{
	public $table ="employee_leave_allow";
	public $acl=false;
	public $title_field="leave";
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Leave','leave_id');
		
		$this->addField('is_yearly_carried_forward')->type('boolean')->defaultValue(false);
		$this->addField('type')->enum(['Paid','Unpaid']);
		$this->addField('is_unit_carried_forward')->type('boolean')->defaultValue(true);
		$this->addField('no_of_leave')->type('int');
		$this->addField('unit')->enum(['Monthly','Yearly']);
		$this->addField('allow_over_quota')->type('boolean')->defaultValue(false);
		$this->addField('effective_date')->type("date");
		$this->addField('previously_carried_leaves')->type('int')->defaultValue(0);
	}
}