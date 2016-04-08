<?php

namespace xepan\hr;

class Model_Post extends \xepan\hr\Model_Document{

	public $status=['Active','InActive'];
	public $actions = [
						'Active'=>['view','edit','deactivate'],
						'InActive' => ['view','edit','delete','activate']
					];

	function init(){
		parent::init();

		$post_j = $this->join('post.document_id');
		
		$post_j->hasOne('xepan\hr\Department','department_id');
		$post_j->hasOne('xepan\hr\ParentPost','parent_post_id');

		$post_j->addField('name');
		$post_j->addField('in_time');
		$post_j->addField('out_time');

		$post_j->hasMany('xepan\hr\Post','parent_post_id',null,'ParentPosts');
		$post_j->hasMany('xepan\hr\Post_Email_Association','post_id',null,'EmailPermissions');
		$post_j->hasMany('xepan\hr\Employee','post_id',null,'Employees');

		$this->addExpression('employee_count')->set($this->refSQL('Employees')->count());
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Post');

		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeDelete',[$this,'deleteEmailAssociation']);

		$this->is([
			'department_id|required'
			]);

	}
	function activate(){
		$this['status']='Active';
		$this->saveAndUnLoad();
		
	}
	function deactivate(){
		$this['status']='InActive';
		$this->saveAndUnLoad();
	}

	function beforeDelete(){
		if($this->ref('Employees')->count()->getOne())
			throw new \Exception("Can not Delete Content First delete Employees", 1);
	}
	function deleteEmailAssociation(){
		$this->ref('EmailPermissions')->deleteAll();
	}
}
