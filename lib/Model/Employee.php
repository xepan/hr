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
		$emp_j->addField('attandance_mode')->enum(['Web Login','Mannual'])->defaultValue('Web Login');
		$emp_j->addField('in_time');
		$emp_j->addField('out_time');

		$emp_j->hasMany('xepan\hr\Employee_Attandance','employee_id',null,'Attendances');
		$emp_j->hasMany('xepan\hr\Employee_Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Employee_Experience','employee_id',null,'Experiences');
		$emp_j->hasMany('xepan\hr\Employee_Document','employee_id',null,'EmployeeDocuments');
		$emp_j->hasMany('xepan\hr\Employee_Movement','employee_id',null,'EmployeeMovements');
		$emp_j->hasMany('xepan\hr\Employee_Salary','employee_id',null,'EmployeeSalary');
		$emp_j->hasMany('xepan\hr\Employee_LeaveAllow','employee_id',null,'EmployeeLeaveAllow');
		
		$this->addExpression('posts')->set(function($m){
            return $m->refSQL('post_id')->fieldQuery('name');
        });

        $this->addExpression('first_email')->set(function($m,$q){
			$x = $m->add('xepan\base\Model_Contact_Email');
			return $x->addCondition('contact_id',$q->getField('id'))
						->addCondition('is_active',true)
						->addCondition('is_valid',true)
						->setLimit(1)
						->fieldQuery('value');
		});
		
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Employee');
		$this->addHook('afterSave',[$this,'throwEmployeeUpdateHook']);
		$this->addHook('afterSave',[$this,'updateTemplates']);
		$this->addHook('beforeDelete',[$this,'deleteQualification']);
		$this->addHook('beforeDelete',[$this,'deleteExperience']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeDocument']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeLedger']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeMovements']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);
		$this->addHook('beforeSave',[$this,'updateEmployeeSalary']);
		$this->addHook('beforeSave',[$this,'updateEmployeeLeave']);
	}

	function throwEmployeeUpdateHook(){
		$this->app->hook('employee_update',[$this]);
	}

	function updateEmployeeSalary(){

		
		if($this->dirty['post_id']){
			
			$temp = $this->ref('post_id')->ref('salary_template_id');

			if($temp->loaded()){
				
				$this->ref('EmployeeSalary')->each(function($m){
					$m->delete();
				});
				
				foreach ($temp->ref('xepan\hr\SalaryTemplateDetails') as $row) {
					$m = $this->add('xepan\hr\Model_Employee_Salary');
					$m['employee_id'] = $this->id;
					$m['salary_id'] = $row['salary_id'];
					$m['amount'] = $row['amount'];
					$m['unit'] = $row['unit'];
					$m->save();
				}
				// throw new \Exception($m->id, 1);
			}
		}
	}

	function updateEmployeeLeave(){
		
		if($this->dirty['post_id']){
			$temp = $this->ref('post_id')->ref('leave_template_id');


			if($temp->loaded()){
				$this->ref('EmployeeSalary')->each(function($m){
					$m->delete();
				});
				
				foreach ($temp->ref('xepan\hr\LeaveTemplateDetail') as $row) {
					$m = $this->add('xepan\hr\Model_Employee_LeaveAllow');
					$m['created_by_id'] = $this->id;
					$m['leave_id'] = $row['leave_id'];
					$m['is_yearly_carried_forward'] = $row['is_yearly_carried_forward'];
					$m['type'] = $row['type'];
					$m['is_unit_carried_forward'] = $row['is_unit_carried_forward'];
					$m['unit'] = $row['unit'];
					$m['allow_over_quota'] = $row['allow_over_quota'];
					$m['no_of_leave'] = $row['no_of_leave'];
					$m->save();
				}
			}
		}
	}

	function updateTemplates(){
		// copy salary and leave templates of posts
		$post = $this->add('xepan\hr\Model_Post')->tryLoadBy('id',$this['post_id']);
		
		if(!$post->loaded())
			return;
		
			$this['salary_template_id'] = $post['salary_template_id'];  
			$this->save(); 
	}

	function afterLoginCheck(){
		
		$this->app->auth->model['last_login_date'] = $this->app->now;
        $this->app->auth->model->save();

		if($this->app->employee['attandance_mode'] != "Web Login") return;

		$attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
		$attan_m->addCondition('employee_id',$this->app->employee->id);
		$attan_m->addCondition('fdate',$this->app->today);
		$attan_m->setOrder('id','desc');
		$attan_m->tryLoadAny();
		
		if(!$attan_m->loaded()){
			$attan_m['employee_id'] = $this->app->employee->id;
			$attan_m['from_date']  = $this->app->now;
		}else{
			$attan_m['to_date']  = null;
		}
		$attan_m->save();


		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$movement->addCondition('employee_id',$this->app->employee->id);
		$movement->addCondition('movement_at',$this->app->today);
		$movement->addCondition('date',$this->app->today);
		$movement->setOrder('movement_at','desc');
		$movement->tryLoadAny();

		if($movement->loaded() && $movement['direction']=='In'){						
			return;
		}else{						
			$model_movement = $this->add('xepan\hr\Model_Employee_Movement');
			$model_movement->addCondition('employee_id',$this->id);
			$model_movement->addCondition('movement_at',$this->app->now);
			// $model_movement->addCondition('type','Attandance');
			$model_movement->addCondition('direction','In');
			$model_movement->save();	
		}
		
	}

	function logoutHook($app, $user, $employee){
		// $movement = $this->add('xepan\hr\Model_Employee_Movement');
		// $movement->addCondition('employee_id',$employee->id);
		// $movement->addCondition('movement_at',$this->app->now);
		// $movement->addCondition('direction','Out');
		// $movement->save();
		// throw new \Exception($movement->id);

	}

	function addActivity($activity_string, $related_document_id=null, $related_contact_id=null, $details=null,$contact_id =null,$document_url=null){
		if(!$contact_id) $contact_id = $this->id;
		$activity = $this->add('xepan\hr\Model_Activity');
		$activity['contact_id'] = $contact_id;
		$activity['activity'] = $activity_string;
		$activity['related_contact_id'] = $related_contact_id;
		$activity['related_document_id'] = $related_document_id;
		$activity['details'] = $details;
		$activity['document_url'] = $document_url;

		$activity->save();
		return $activity;
	}

	function communicationCreatedNotify($app,$comm){
		if(($comm['direction']=='In' && !$comm['from_id']) || ($comm['direction']=='Out' && !$comm['to_id']))
			return;

		$related_contact_id=null;
		$comm_model=null;
		$msg = $comm['from'].' Communicated '. $comm['to'];
		if($comm['direction']=='In'){
			$related_contact_id = $comm['from_id'];
			$comm_model = $comm->ref('from_id');
		}else{
			$related_contact_id = $comm['to_id'];
			$comm_model = $comm->ref('to_id');
		}

		$activity  = $this->addActivity($msg,null,$related_contact_id,null,$this->id,'xepan_communication_viewer&comm_id='.$comm->id);
		$activity->notifyWhoCan('communication','Active,InActive' ,$comm_model);
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
		$this->app->employee
            ->addActivity("Employee '".$this['name']."' has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
		if(($user = $this->ref('user_id')) && $user->loaded()) $user->deactivate();
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Employee '".$this['name']."' is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
            ->notifyWhoCan('deactivate','Active',$this);
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
