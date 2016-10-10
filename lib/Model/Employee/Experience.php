<?php
namespace xepan\hr;
class Model_Employee_Experience extends \xepan\base\Model_Table{
	public $table="experience";

	public $acl=false;
	function init(){
		parent::init();
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('name')->caption('Company name');
		$this->addField('department');
		$this->addField('company_branch');
		$this->addField('salary');
		$this->addField('designation');
		$this->addField('duration');
		
	}
}