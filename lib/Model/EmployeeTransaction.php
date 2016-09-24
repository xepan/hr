<?php

namespace xepan\hr;

class Model_EmployeeTransaction extends \xepan\base\Model_Table{
	public $table = "employee_transaction";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		
		$this->addField('name');
		$this->addField('type')->setValueList('Due'=>'Due','Paid'=>'Paid');
		$this->addField('narration')->type('text');
	}
}