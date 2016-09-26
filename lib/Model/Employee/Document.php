<?php

namespace xepan\hr;

class Model_Employee_Document extends \xepan\base\Model_Table{
	public $table="employee_documents";

	public $acl='xepan\hr\Model_Employee';
	
	function init(){
		parent::init();
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('name');
		$this->add('xepan/filestore/Field_File','employee_document_id');

		$this->addExpression('filename')->set(function($m,$q){
			return $m->refSQL('employee_document_id')->fieldQuery('original_filename');
		});
	}
}