<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	public $actions=[
		'Active'=>['view','edit','delete'],
		'InActive'=>['view','edit','delete']
	];

	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User');
		$emp_j->hasOne('xepan\hr\Department','department_id');
		$emp_j->hasOne('xepan\hr\Post','post_id');

		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity

		$emp_j->hasMany('xepan\hr\Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Experience','employee_id',null,'Experiences');
		$emp_j->hasMany('xepan\hr\EmployeeDocument','employee_id',null,'EmployeeDocuments');
		
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Employee');

		$this->addHook('beforeSave',$this);
		$this->addHook('beforeDelete',$this);
	}

	function addActivity($activity_string, $related_document_id=null, $related_contact_id=null, $details=null,$contact_id =null){
		if(!$contact_id) $contact_id = $this->id;
		$activity = $this->add('xepan\hr\Model_Activity');
		$activity['contact_id'] = $contact_id;
		$activity['activity'] = $activity_string;
		$activity['related_contact_id'] = $related_contact_id;
		$activity['related_document_id'] = $related_document_id;
		$activity['details'] = $details;

		$activity->save();
		return $activity;
	}
	function getPermissionEmail(){
		$permission_email = $this->ref('EmailPermissions')->_dsql()->del('fields')->field('emailsetting_id')->getAll();
		return iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($permission_email)),false);
	}

	function removePermissionEmail(){
		$emails= $this->ref('EmailPermissions');
		$emails->deleteAll();
	}

	function beforeSave($m){}

	function beforeDelete($m){
		$m->ref('Qualifications')->deleteAll();
		$m->ref('Experiences')->deleteAll();
		$m->ref('EmployeeDocuments')->deleteAll();
	}
}
