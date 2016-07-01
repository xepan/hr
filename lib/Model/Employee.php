<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	public $status=[
		'Active',
		'InActive'
	];
	public $actions=[
		'Active'=>['view','edit','delete','deactivate','communication'],
		'InActive'=>['view','edit','delete','activate','communication']
	];

	function init(){
		parent::init();
		$this->getElement('post')->destroy();
		$this->getElement('created_by_id')->defaultValue(@$this->app->employee->id);
		$emp_j = $this->join('employee.contact_id');

		// $emp_j->hasOne('xepan\base\User',null,'username'); // Now in Contact
		$emp_j->hasOne('xepan\hr\Department','department_id')->sortable(true);
		$emp_j->hasOne('xepan\hr\Post','post_id');
		
		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity
		$emp_j->addField('offer_date')->type('date')->sortable(true);
		$emp_j->addField('doj')->caption('Date of Joining')->type('date')->defaultValue(@$this->app->now)->sortable(true);
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
		$this->addHook('beforeSave',[$this,'updateSearchString']);
	}

	function throwEmployeeUpdateHook(){
		$this->app->hook('employee_update',[$this]);
	}

	function afterLoginCheck(){		
		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$movement->addCondition('employee_id',$this->app->employee->id);
		$movement->addCondition('date',$this->app->today);
		$movement->setOrder('time','desc');
		$movement->tryLoadAny();

		if($movement->loaded() && $movement['direction']=='In'){						
			return;
		}else{						
			$model_movement = $this->add('xepan\hr\Model_Employee_Movement');
			$model_movement->addCondition('employee_id',$this->id);
			$model_movement->addCondition('time',$this->app->now);
			$model_movement->addCondition('type','Attandance');
			$model_movement->addCondition('direction','In');
			$model_movement->save();	
		}
		
	}

	function logoutHook($app, $user, $employee){
		// $movement = $this->add('xepan\hr\Model_Employee_Movement');
		// $movement->addCondition('employee_id',$employee->id);
		// $movement->addCondition('time',$this->app->now);
		// $movement->addCondition('type','Attandance');
		// $movement->addCondition('direction','Out');
		// $movement->save();
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
		$this->save();
		if(($user = $this->ref('user_id')) && $user->loaded()) $user->deactivate();
	}

	function activate(){
		$this['status']='Active';
		$this->save();
		if(($user = $this->ref('user_id')) && $user->loaded()) $user->activate();
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['contact_id'];
		$search_string .=" ". $this['notified_till'];
		$search_string .=" ". $this['offer_date'];
		$search_string .=" ". $this['doj'];
		$search_string .=" ". $this['contract_date'];
		$search_string .=" ". $this['leaving_date'];
		$search_string .=" ". $this['mode'];
		$search_string .=" ". $this['in_time'];
		$search_string .=" ". $this['out_time'];
		$search_string .=" ". $this['first_name'];
		$search_string .=" ". $this['last_name'];

		if($this->loaded()){
			$qualification = $this->ref('Qualifications');
			foreach ($qualification as $qualification_detail) {
				$search_string .=" ". $qualification_detail['name'];
				$search_string .=" ". $qualification_detail['qualificaton_level'];
				$search_string .=" ". $qualification_detail['remarks'];
			}
		}

		if($this->loaded()){
			$experience = $this->ref('Experiences');
			foreach ($experience as $experience_detail) {
				$search_string .=" ". $experience_detail['name'];
				$search_string .=" ". $experience_detail['department'];
				$search_string .=" ". $experience_detail['company_branch'];
				$search_string .=" ". $experience_detail['designation'];
			}
		}

		if($this->loaded()){
			$employeedocument = $this->ref('EmployeeDocuments');
			foreach ($experience as $employeedocument_detail) {
				$search_string .=" ". $employeedocument_detail['name'];
			}
		}

		if($this->loaded()){
			$employeemovement = $this->ref('EmployeeMovements');
			foreach ($experience as $employeemovement_detail) {
				$search_string .=" ". $employeemovement_detail['time'];
				$search_string .=" ". $employeemovement_detail['type'];
				$search_string .=" ". $employeemovement_detail['direction'];
				$search_string .=" ". $employeemovement_detail['reason'];
				$search_string .=" ". $employeemovement_detail['narration'];
			}
		}

		$this['search_string'] = $search_string;
		
	}

}
