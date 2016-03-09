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

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);

		$this->is([
				'production_level|int|>0'
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
	function beforeSave($m){
		
		$dept_old=$this->add('xepan\hr\Model_Department');
		
		if($this->loaded())
			$dept_old->addCondition('id','<>',$this->id);
		$dept_old->tryLoadAny();

		if($dept_old['name'] == $this['name'])
			throw $this->exception('Department is Allready Exist');
	}


	function beforeDelete($m){
		$post_count = $m->ref('Post')->count()->getOne();
		$emp_count = $m->ref('Employees')->count()->getOne();
		
		if($post_count or $emp_count)
			throw $this->exception('Cannot Delete,first delete Post, Employees ');	
	}
}