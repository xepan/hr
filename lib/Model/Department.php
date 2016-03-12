<?php

namespace xepan\hr;

class Model_Department extends \xepan\hr\Model_Document{

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
		$dep_j->addField('is_system')->type('boolean')->defaultValue(false)->system(true);

		$this->addExpression('posts_count')->set($this->refSQL('Post')->count());

		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Department');

		$this->addHook('beforeDelete'$this);		
		$this->is([
				'production_level|int|>0',
				'name|unique_in_epan|to_trim|required'
			]);
	}

	function activate(){
		$this['status']='Active';
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->saveAndUnload();
	}

	function beforeDelete(){
		$posts_count=$this->ref('Post')->count()->getOne();
		$employee_count=$this->ref('Employees')->count()->getOne();

		if($posts_count or $employee_count){
			throw new \Exception("Department Can not be deleted its content Post And Employee Delete First", 1);
		}

	}
}