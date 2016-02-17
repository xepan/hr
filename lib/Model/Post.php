<?php
namespace xepan\hr;
class Model_Post extends \Model_Table{
	public $table="post";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Department');
		$this->addField('name');
		$this->addField('status')->enum(['Active','DeActive']);

		$this->hasMany('xepan\hr\Employee',null,null,'Employees');
	}
}