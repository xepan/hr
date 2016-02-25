<?php

namespace xepan\hr;

class Model_Department extends \xepan\base\Model_Document{

	function init(){
		parent::init();

		$dep_j = $this->join('department.document_id');
		$dep_j->addField('name');
		$dep_j->addField('production_level');

		$dep_j->hasMany('xepan\hr\Post','department_id',null,'Post');
		$dep_j->hasMany('xepan\hr\Employee','department_id',null,'Employees');

		$this->addExpression('posts_count')->set($this->refSQL('Post')->count());

		$this->addCondition('type','Department');
	}
}