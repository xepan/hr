<?php
namespace xepan\hr;
class Employee extends \Model_Table{
	public $table="employee";
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Contact');
		$this->hasOne('xepan\base\Post');
		$this->addField('name');
		$this->addField('created_at')->type('date');
		$this->addField('status')->enum(['Active','DeActive']);
		$this->addField('email');
		$this->addField('contact_no');

	}
}