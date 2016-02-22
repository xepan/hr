<?php

namespace xepan\hr;

class Model_Post extends \xepan\base\Model_Table{
	public $table="post";
	function init(){
		parent::init();
		// $post_j = $this->join('post.document_id');
		
		$this->hasOne('xepan\base\Epan');
		$this->hasOne('xepan\hr\Department');

		$this->addField('name');

		$this->hasMany('xepan\hr\Employee',null,null,'Employees');

		$this->addExpression('employee')->set($this->refSQL('Employees')->count());

	}
}
