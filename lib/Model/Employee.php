<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	function init(){
		parent::init();

		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\User');
		$emp_j->hasOne('xepan\hr\Department','department_id');
		$emp_j->hasOne('xepan\hr\Post','post_id');

		$emp_j->hasMany('xepan\hr\Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Experience','employee_id',null,'Experiences');
		
		$this->addCondition('type','Employee');
	}

	function addActivity($activity, $related_contact_id=null, $related_document_id=null, $details=null,$contact_id =null){
		if(!$contact_id) $contact_id = $this->id;
		return $this->app->epan->addActivity($contact_id, $activity, $related_contact_id, $related_document_id, $details);
	}
}
