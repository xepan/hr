<?php


namespace xepan\hr;

/**
* 
*/
class Model_Employee_LeaveAllow extends xepan\base\Model_Table{
	public $table ="employee_leave_allow";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Leave','leave_id');
		
		$this->addField('is_yearly_carried_forward')->type('boolean')->defalutValue(false);
		$this->addField('type')->enum(['Paid','Unpaid']);
		$this->addField('is_unit_carried_forward')->type('boolean')->defalutValue(true);
		$this->addField('no_of_leave')->type('int');
		$this->addField('unit')->enum(['Monthly','Weekly','Quaterly','Yearly']);
		$this->addField('allow_over_quota')->type('boolean')->defalutValue(false);

	}
}