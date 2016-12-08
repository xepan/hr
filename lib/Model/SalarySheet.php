<?php

namespace xepan\hr;

class Model_SalarySheet extends \xepan\hr\Model_SalaryAbstract{
	
	function init(){
		parent::init();

		$this->addCondition('type','SalarySheet');
	}
}