<?php

namespace xepan\hr;

class Model_EmployeeDocument extends \xepan\base\Model_Table{
	public $table="employee_documents";

	public $acl='xepan\hr\Model_Employee';
	
	function init(){
		parent::init();
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('name');
		$this->add('filestore/Field_Image','employee_document_id');
	}
}