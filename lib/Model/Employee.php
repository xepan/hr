<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	public $actions=[
		'Active'=>['view','edit','delete','deactivate'],
		'InActive'=>['view','edit','delete','activate']
	];

	function init(){
		parent::init();
		$this->getElement('post')->destroy();
		$this->getElement('created_by_id')->defaultValue(@$this->app->employee->id);
		$emp_j = $this->join('employee.contact_id');

		// $emp_j->hasOne('xepan\base\User',null,'username');
		$emp_j->hasOne('xepan\hr\Department','department_id');
		$emp_j->hasOne('xepan\hr\Post','post_id');
		
		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity
		$emp_j->addField('offer_date')->type('date')->sortable(true);
		$emp_j->addField('doj')->caption('Date of Joining')->type('date');
		$emp_j->addField('contract_date')->type('date');
		$emp_j->addField('leaving_date')->type('date');
		$emp_j->addField('mode')->enum(['First_time_login','Mannual']);
		$emp_j->addField('in_time');
		$emp_j->addField('out_time');

		$emp_j->hasMany('xepan\hr\Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Experience','employee_id',null,'Experiences');
		$emp_j->hasMany('xepan\hr\EmployeeDocument','employee_id',null,'EmployeeDocuments');
		$emp_j->hasMany('xepan\hr\Employee_Movement','employee_id',null,'EmployeeMovements');
		
		$this->addExpression('posts')->set(function($m){
            return $m->refSQL('post_id')->fieldQuery('name');
        });
		
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Employee');
		$this->addHook('afterSave',[$this,'throwEmployeeUpdateHook']);
		$this->addHook('beforeDelete',[$this,'deleteQualification']);
		$this->addHook('beforeDelete',[$this,'deleteExperience']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeDocument']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeLedger']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeMovements']);
	}

	function throwEmployeeUpdateHook(){
		$this->app->hook('employee_update',[$this]);
	}

	function afterLoginCheck(){
		$model_employee_movement = $this->add('xepan\hr\Model_Employee_Movement');
		$model_employee_movement->addCondition('employee_id',$this->id);
		$model_employee_movement->addCondition('time','>=',$this->app->today);
		$model_employee_movement->tryLoadAny();

		if($model_employee_movement->loaded()){
			return;
		}
		else{
			
			$movement = $this->add('xepan\hr\Model_Employee_Movement');
			$movement->addCondition('employee_id',$this->id);
			$movement->addCondition('time',$this->app->now);
			$movement->addCondition('type','Attandance');
			$movement->addCondition('direction','In');
			$movement->save();
		}	
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

	function deleteEmployeeLedger(){
		$account=$this->add('xepan\accounts\Model_Ledger');
		$account->addCondition('contact_id',$this->id);
		$account->tryLoadAny();
		if($account->loaded()){
			$account->delete();
		}
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
	function deleteEmployeeMovements(){
		$this->ref('EmployeeMovements')->deleteAll();	
	}

	function manageMovement(){
		if($this->loaded())
			throw new \Exception("Employee Model Must be Loaded", 1);
		/*check employee mode == First_time_login */
			/*if yes*/
			if($this['mode']=='First_time_login'){
				$m=$this->add('xepan\hr\Model_Employee_Movement');
				$m['employee_id']=$this->id;
				$m['date']=$this->api->today;
				$m['time']=date('H:i:s');		
				$m['type']='Attandance';		
				$m['direction']='In';
				$m->save();		
				
			}
			/*if No*/
	}

	function deactivate(){
		$this['status']='InActive';
		$this->ref('user_id')
		$this->save();
	}

	function activate(){
		$this['status']='Active';
		$this->save();
	}

}
