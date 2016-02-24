<?php

namespace xepan\hr;

class Model_Post extends \xepan\base\Model_Document{
	function init(){
		parent::init();

		$post_j = $this->join('post.document_id');
		
		// $post_j->hasOne('xepan\base\Epan');
		$post_j->hasOne('xepan\hr\Department','department_id');

		$post_j->addField('name');

		$post_j->hasMany('xepan\hr\Employee','post_id',null,'Employees');

		$this->addExpression('employee_count')->set($this->refSQL('Employees')->count());

	}
}
