<?php

namespace xepan\hr;

class Model_SalaryPayment extends \xepan\hr\Model_SalaryAbstract{
	
	function init(){
		parent::init();

		$this->addCondition('type','SalaryPayment');
	}
}