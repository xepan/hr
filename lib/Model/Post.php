<?php

namespace xepan\hr;

class Post extends xepan\base\Model_Table{
	public $table="post";
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Department');
		$this->addField('name');
		$this->addField('status')->enum(['Active','DeActive']);

		$this->hasMany('xepan\hr\Employee',null,null,'Employees');
	}
}