<?php

namespace xepan\hr;

class Model_Department extends \xepan\base\Model_Document{

	public $status=['Active','InActive'];
	
	public $actions = [
		'Active'=>['view','edit','delete','deactivate'],
		'InActive' => ['view','edit','delete','activate']
	];

	function init(){
		parent::init();

		$dep_j = $this->join('department.document_id');
		$dep_j->addField('name');
		$dep_j->addField('production_level');

		$dep_j->hasMany('xepan\hr\Post','department_id',null,'Post');
		$dep_j->hasMany('xepan\hr\Employee','department_id',null,'Employees');

		$this->addExpression('posts_count')->set($this->refSQL('Post')->count());

		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Department');
	}

	function page_activate($p){
		$p->add('View')->set('Hello');
		$f = $p->add('Form');
		$f->addField('name');
		$f->addField('password');
		$f->onSubmit(function($f){
			$f->displayError('name','Oops');
		});

		$btn = $p->add('Button')->set($this['name']);
		if($btn->isClicked()){
			return true;
		}
	}
}