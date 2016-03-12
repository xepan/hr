<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	public $actions=[
		'Active'=>['view','edit','delete'],
		'InActive'=>['view','edit','delete']
	];

	function init(){
		parent::init();
		$this->getElement('post')->destroy();
		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User',null,'username');
		$emp_j->hasOne('xepan\hr\Department','department_id');
		$emp_j->hasOne('xepan\hr\Post','post_id');

		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity
		$emp_j->addField('offer_date')->type('date');
		$emp_j->addField('doj')->caption('Date of Joining')->type('date');
		$emp_j->addField('contract_date')->type('date');
		$emp_j->addField('leving_date')->type('date');

		$emp_j->hasMany('xepan\hr\Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Experience','employee_id',null,'Experiences');
		$emp_j->hasMany('xepan\hr\EmployeeDocument','employee_id',null,'EmployeeDocuments');
		
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Employee');

		$this->addHook('beforeDelete',[$this,'deleteQualification']);
		$this->addHook('beforeDelete',[$this,'deleteExperience']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeDocument']);
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

	function deleteQualification(){
		$this->ref('Qualifications')->deleteAll();	
	}
	function deleteExperience(){
		$this->ref('Experiences')->deleteAll();	
	}
	function deleteEmployeeDocument(){
		$this->ref('EmployeeDocuments')->deleteAll();	
	}	
	
}
