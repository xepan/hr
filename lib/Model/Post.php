<?php

namespace xepan\hr;

class Model_Post extends \xepan\base\Model_Document{
	function init(){
		parent::init();
		$post_j = $this->join('post.document_id');
		
		$post_j->hasOne('xepan\base\Epan');
		$post_j->hasOne('xepan\hr\Department');

		$post_j->addField('name');

		// $post_j->hasMany('xepan\hr\Employee',null,null,'Employees');

		// $post_j->addExpression('employee')->set($this->refSQL('Employees')->count());

	}
}
