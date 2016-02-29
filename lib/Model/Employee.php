<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
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
		
		$this->addCondition('type','Employee');
	}

	function addActivity($activity, $related_document_id=null, $related_contact_id=null, $details=null,$contact_id =null){
		if(!$contact_id) $contact_id = $this->id;
		$activity = $this->add('xepan\hr\Model_Activity');
		$activity['conatc_id'] = $contact_id;
		$activity['activity'] = $activity;
		$activity['related_contact_id'] = $related_contact_id;
		$activity['related_document_id'] = $related_document_id;
		$activity['details'] = $details;

		$activity->save();
		return $activity;
	}
}
