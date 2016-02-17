<?php
namespace xepan\hr;
class Model_Employee extends \Model_Table{
	public $table="employee";
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Contact');
		$this->hasOne('xepan\base\Post');
		$this->addField('name');
		$this->addField('created_at')->type('date');
		$this->addField('status')->enum(['Active','Inactive']);
		$this->addField('email');
		$this->addField('contact_no');

	}
}