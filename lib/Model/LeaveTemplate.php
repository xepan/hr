<?php

namespace xepan\hr;

class Model_LeaveTemplate extends \xepan\base\Model_Table{
	public $table= "leave_template";
	public $actions = ['*'=>['view','edit','delete']];
 	public $acl_type = "LeaveTemplate";
 			
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->addField('name');
		
		$this->hasMany('xepan\hr\LeaveTemplateDetail','leave_template_id');
		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){
		// first check leave is apply on post or employee not
		$post = $this->add('xepan\hr\Model_Post');
		$post->addCondition('leave_template_id',$this->id);
		if($post->count()->getOne()){
			throw new \Exception("first remove leave template from post");
		}

		$model = $this->add('xepan\hr\Model_LeaveTemplateDetail');
		$model->addCondition('leave_template_id',$this->id)
				->deleteAll();
	}
}