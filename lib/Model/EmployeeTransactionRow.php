<?php

namespace xepan\hr;

class Model_EmployeeTransactionRow extends \xepan\base\Model_Table{
	public $table = "employee_transaction_row";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\EmployeeTransaction','employee_transaction_id');

		$this->addField('name');
		$this->addField('narration')->type('text');
		$this->addField('due');
		$this->addField('paid');
	}
}