<?php

namespace xepan\hr;

class Model_Department extends \xepan\base\Model_Document{

	public $table="department";

	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan');
		$this->addField('name');
		$this->addField('production_level');
		$this->addField('status')->enum(['Active','Inactive']);

		$this->hasMany('xepan\hr\Post',null,null,'Post');

		$this->addExpression('posts')->set(function($m,$q){
			return '3';
		// 	return $m->refSQL('xepan\hr\Post')->addCondition('department_id',$this->id)->count('id');
		});
	}
}