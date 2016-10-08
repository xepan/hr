<?php


namespace xepan\hr;

class Model_Employee_Active extends Model_Employee {
	function init(){
		parent::init();
		$this->addCondition('status','Active');
	}
}