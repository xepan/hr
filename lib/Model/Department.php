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
		$dep_j->addField('name')->sortable(true);
		$dep_j->addField('production_level')->sortable(true);

		$dep_j->hasMany('xepan\hr\Post','department_id',null,'Posts');
		$dep_j->hasMany('xepan\hr\Employee','department_id',null,'Employees');
		$dep_j->addField('is_system')->type('boolean')->defaultValue(false)->system(true);
		$dep_j->addField('is_outsourced')->type('boolean')->defaultValue(false);

		$this->addExpression('posts_count')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Post',['table_alias'=>'dept_post_count'])->addCondition('department_id',$m->getElement('id'))->count();
			
		})->sortable(true);

		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Department');

		$this->addHook('beforeDelete',[$this,'checkForPostsAndEmployees']);		
		
		$this->is([
				'name|unique_in_epan|to_trim|required',
				'production_level|required?Production Level must be filled|int|>0'
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

	function checkForPostsAndEmployees(){
		$posts_count=$this->ref('Posts')->count()->getOne();
		$employee_count=$this->ref('Employees')->count()->getOne();

		if($posts_count or $employee_count){
			throw new \Exception("Department Can not be deleted its content Post And Employee Delete First", 1);
		}
	}
}