<?php
namespace xepan\hr;
class Model_Qualification extends \xepan\base\Model_Table{
	public $table="qualification";

	public $acl=false;
	
	function init(){
		parent::init();
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('name')->caption('Qualification');
		$this->addField('qualificaton_level');
		$this->addField('remarks');

	}
}