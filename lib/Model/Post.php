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
		
		$post_j->hasOne('xepan\hr\Department','department_id')->sortable(true);
		$post_j->hasOne('xepan\hr\ParentPost','parent_post_id');

		$post_j->addField('name')->sortable(true);
		$post_j->addField('in_time');
		$post_j->addField('out_time');

		$post_j->hasMany('xepan\hr\Post','parent_post_id',null,'ParentPosts');
		$post_j->hasMany('xepan\hr\Post_Email_Association','post_id',null,'EmailPermissions');
		$post_j->hasMany('xepan\hr\Employee','post_id',null,'Employees');

		$this->addExpression('employee_count')->set($this->refSQL('Employees')->count())->sortable(true);
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Post');

		$this->addHook('beforeSave',[$this,'changeEmployeeInOutTimes']);
		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeDelete',[$this,'deleteEmailAssociation']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);

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

	function changeEmployeeInOutTimes(){
		$model_employee = $this->add('xepan\hr\Model_Employee')->addCondition('post_id',$this->id);
		
		if($this->dirty['in_time']){
			foreach ($model_employee as $emp) {
				$emp['in_time'] = $this['in_time'];
				$emp->save();
			}
		}

		if($this->dirty['out_time']){
			foreach ($model_employee as $emp) {
				$emp['out_time'] = $this['out_time'];
				$emp->save();
			}
		}
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['name'];
		$search_string .=" ".$this['in_time'];
		$search_string .=" ".$this['out_time'];

		$parent_post = $this->ref('ParentPosts');
		foreach ($parent_post as $parent_post_detail) 
		{
			$search_string .=" ". $parent_post_detail['name'];
			$search_string .=" ". $parent_post_detail['in_time'];
			$search_string .=" ". $parent_post_detail['out_time'];
		}

		$employees = $this->ref('Employees');
		foreach ($employees as $employees_detail) {
			$search_string .=" ". $employees_detail['contract_date'];
			$search_string .=" ". $employees_detail['doj'];
		}

		$this['search_string'] = $search_string;
	}

}
