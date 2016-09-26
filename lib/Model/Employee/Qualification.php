<?php
namespace xepan\hr;
class Model_Employee_Qualification extends \xepan\base\Model_Table{
	public $table="qualification";

	public $acl=false;
	
	function init(){
		parent::init();
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('name')->caption('Qualification')->hint('Name of Degree/Course');
		$this->addField('qualificaton_level')->hint('Graduation/Master/Phd/Others');
		$this->addField('remarks')->type('text');

	}
}