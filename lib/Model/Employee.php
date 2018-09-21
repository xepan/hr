<?php

namespace xepan\hr;

class Model_Employee extends \xepan\base\Model_Contact{
	
	public $status=[
		'Active',
		'InActive',
		"DeactivateRequest"
	];

	public $actions=[
		'Active'=>['view','edit','delete','deactivate','communication'],
		'InActive'=>['view','edit','delete','activate','communication'],
		'DeactivateRequest'=>['view','edit','delete','deactivate','activate']
	];

	public $contact_type = "Employee";
	
	function init(){
		parent::init();
		$this->getElement('post')->destroy();
		$this->getElement('created_by_id')->defaultValue(@$this->app->employee->id);
		$emp_j = $this->join('employee.contact_id');

		$emp_j->hasOne('xepan\base\GraphicalReport','graphical_report_id');
		
		$emp_j->hasOne('xepan\hr\Department','department_id')->sortable(true)->display(array('form' => 'xepan\commerce\DropDown'));
		$emp_j->hasOne('xepan\hr\Post','post_id')->display(array('form' => 'xepan\commerce\DropDown'));
		
		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity
		$emp_j->addField('offer_date')->type('date')->sortable(true);
		$emp_j->addField('doj')->caption('Date of Joining')->type('date')->defaultValue(@$this->app->now)->sortable(true);
		$emp_j->addField('contract_date')->type('date');
		$emp_j->addField('leaving_date')->type('date');
		$emp_j->addField('allow_login_from_anywhere')->type('boolean')->defaultValue(false);
		$emp_j->addField('attandance_mode')->enum(['Web Login','Mannual'])->defaultValue('Web Login');
		$emp_j->addField('in_time')->display(array('form' => 'TimePicker'));
		$emp_j->addField('out_time')->display(array('form' => 'TimePicker'));
		$emp_j->addField('finacial_permit_limit')->type('text');
		$emp_j->addField('salary_payment_type')->enum(['production_unit','monthly','hourly'])->defaultValue('monthly');
		
		$emp_j->addField('track_geolocation')->type('boolean')->defaultValue(false);
		$emp_j->addField('last_latitude')->defaultValue(NUll);
		$emp_j->addField('last_longitude')->defaultValue(NUll);
		$emp_j->addField('last_geolocation_update')->type('datetime')->defaultValue(NUll);
		$emp_j->addField('last_location');

		$emp_j->hasMany('xepan\hr\Employee_Attandance','employee_id',null,'Attendances');
		$emp_j->hasMany('xepan\hr\Employee_Qualification','employee_id',null,'Qualifications');
		$emp_j->hasMany('xepan\hr\Employee_Experience','employee_id',null,'Experiences');
		$emp_j->hasMany('xepan\hr\Employee_Document','employee_id',null,'EmployeeDocuments');
		$emp_j->hasMany('xepan\hr\Employee_Movement','employee_id',null,'EmployeeMovements');
		$emp_j->hasMany('xepan\hr\Employee_Salary','employee_id',null,'EmployeeSalary');
		$emp_j->hasMany('xepan\hr\Employee_LeaveAllow','employee_id',null,'EmployeeLeaveAllow');
		$emp_j->hasMany('xepan\hr\EmployeeDepartmentalAclAssociation','employee_id');
		
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

		$this->addExpression('check_login')->set(function($m,$q){
			 $attan_m = $this->add("xepan\hr\Model_Employee_Attandance")
						->addCondition('employee_id',$m->getElement('id'))
						->addCondition('fdate',$this->app->today)
						->setOrder('id','desc')
						->setLimit(1)
						;

			return $q->expr('IFNULL([0],0)',[$attan_m->fieldQuery('id')]);
		})->type('int');
		
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Employee');
		$this->addHook('afterSave',[$this,'throwEmployeeUpdateHook']);
		// $this->addHook('afterInsert',[$this,'updateTemplates']);
		$this->addHook('beforeDelete',[$this,'deleteQualification']);
		$this->addHook('beforeDelete',[$this,'deleteExperience']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeDocument']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeLedger']);
		$this->addHook('beforeDelete',[$this,'deleteEmployeeMovements']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);
		// $this->addHook('afterSave',[$this,'updateEmployeeSalary']);
		// $this->addHook('afterSave',[$this,'updateEmployeeLeave']);
		$this->addHook('beforeSave',[$this,'checkLimits']);
	}

	function post(){
		return $this->ref('post_id');
	}

	function checkLimits(){
		$extra_info = $this->app->recall('epan_extra_info_array',false);
		// value zero/0 means unlimited employee add 
        if((isset($extra_info ['specification']['Employee Limit'])) AND ($extra_info ['specification']['Employee Limit'] > 0)){
        	$emp_count = $this->add('xepan\hr\Model_Employee');
        	if($this->loaded())
        		$emp_count->addCondition('id','<>',$this->id);

			$emp_count = $emp_count->count()->getOne();
        	if($emp_count >= $extra_info ['specification']['Employee Limit']){
        		throw $this->exception("Sorry ! You cannot add more employees. Your Employee Limit is over")
        				->addMoreInfo('Employee Count',$emp_count)
        				->addMoreInfo('Employee Limit',$extra_info ['specification']['Employee Limit'])
        			;
        	}
        }
	}

	function getActiveEmployeeIds(){
		$emp = $this->add('xepan\hr\Model_Employee')->addCondition('status','Active');
		$emp_ids = [];
		foreach ($emp->getRows() as $emp){
			$emp_ids [] = $emp['id'];
		}

		return $emp_ids;
	}

	function throwEmployeeUpdateHook(){
		$this->app->hook('employee_update',[$this]);
	}

	function updateEmployeeSalary($force_update=0){

		if(isset($this->app->employee_post_id_changed) || $force_update){
			$this->ref('EmployeeSalary')->each(function($m){
				$m->delete();
			});

			$temp = $this->ref('post_id')->ref('salary_template_id');
			if($temp->loaded()){
				foreach ($temp->ref('xepan\hr\SalaryTemplateDetails') as $row) {
					$m = $this->add('xepan\hr\Model_Employee_Salary');
					$m['employee_id'] = $this->id;
					$m['salary_id'] = $row['salary_id'];
					$m['amount'] = $row['amount'];
					$m['unit'] = $row['unit'];
					$m->save();
				}
			}
		}
	}

	function updateEmployeeLeave($force_update=0){
		
		if(isset($this->app->employee_post_id_changed) || $force_update){
			$temp = $this->ref('post_id')->ref('leave_template_id');

			$this->ref('EmployeeLeaveAllow')->each(function($m){
				$m->delete();
			});
			
			if($temp->loaded()){
				foreach ($temp->ref('xepan\hr\LeaveTemplateDetail') as $row) {
					$m = $this->add('xepan\hr\Model_Employee_LeaveAllow');
					$m['employee_id'] = $this->id;
					// $m['created_by_id'] = $this->id;
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

	// function updateTemplates(){
	// 	// copy salary and leave templates of posts
	// 	$post = $this->add('xepan\hr\Model_Post')->tryLoadBy('id',$this['post_id']);
		
	// 	if(!$post->loaded())
	// 		return;
		
	// 		$this['salary_template_id'] = $post['salary_template_id'];  
	// 		$this->save(); 
	// }

	function afterLoginCheck(){

		// Check if login is allowed for this user from out side IPs
		// TODO



		// check if attendance is allowed outside this IP
		$ip_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'ip'=>"line"
										],
							'config_key'=>'HR_ALLOWED_IP_4_ATTENDANCE',
							'application'=>'hr'
						]);
		$ip_model->tryLoadAny();
			
		$this->app->auth->model['last_login_date'] = $this->app->now;
        $this->app->auth->model->save();
		
		if(trim($ip_model['ip']) && !in_array($_SERVER['REMOTE_ADDR'], explode(",", $ip_model['ip'])) ){
			// throw new \Exception("Not found ". $_SERVER['REMOTE_ADDR']. ' in '. print_r(explode(",", $ip_model['ip']),true), 1);
			return;	
		}

		if($this->app->employee['attandance_mode'] != "Web Login") return;

		$attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
		$attan_m->addCondition('employee_id',$this->app->employee->id);
		$attan_m->addCondition('fdate',$this->app->today);
		$attan_m->setOrder('id','desc');
		$attan_m->tryLoadAny();

		if(!$attan_m->loaded()){
			$attan_m['from_date']  = $this->app->now;
			$attan_m['to_date']  = $this->app->today." ".$this['out_time'];
		}elseif(date('Y-m-d H:i:s',strtotime($attan_m['to_date'])) <= date('Y-m-d H:i:s',strtotime($this->app->today." ".$this['out_time'])) ){
			$attan_m['to_date']  = $this->app->today." ".$this['out_time'];
		}

		$attan_m['total_movement_in'] = $attan_m['total_movement_in'] + 1;
		$attan_m['is_holiday']  = $attan_m->isHoliday($attan_m['fdate']);
		$attan_m->save();


		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$movement->addCondition('employee_id',$this->app->employee->id);
		$movement->addCondition('date',$this->app->today);
		$movement->setOrder('movement_at','desc');
		$movement->tryLoadAny();

		if($movement->loaded() && $movement['direction']=='In'){
			// Already last movement is set as IN no need to do anything else
			return;
		}else{
			$model_movement = $this->add('xepan\hr\Model_Employee_Movement');
			$model_movement->addCondition('employee_id',$this->id);
			$model_movement->addCondition('movement_at',$this->app->now);
			$model_movement->addCondition('direction','In');
			$model_movement->save();	
		}
		
	}

	// function logoutHook($app, $user, $employee){
	// 	// $movement = $this->add('xepan\hr\Model_Employee_Movement');
	// 	// $movement->addCondition('employee_id',$employee->id);
	// 	// $movement->addCondition('movement_at',$this->app->now);
	// 	// $movement->addCondition('direction','Out');
	// 	// $movement->save();
	// 	// throw new \Exception($movement->id);

	// }

	function addActivity($activity_string, $related_document_id=null, $related_contact_id=null, $details=null,$contact_id =null,$document_url=null,$score=0){
		if(!$contact_id) $contact_id = $this->id;
		$activity = $this->add('xepan\hr\Model_Activity');
		$activity['contact_id'] = $contact_id;
		$activity['activity'] = $activity_string;
		$activity['related_contact_id'] = $related_contact_id;
		$activity['related_document_id'] = $related_document_id;
		$activity['details'] = $details;
		$activity['document_url'] = $document_url;
		$activity['score'] = $score;

		$activity->save();
		return $activity;
	}

	function communicationCreatedNotify($app,$comm){

		if(isset($this->app->skipActivityCreate) && $this->app->skipActivityCreate) return;

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

		$activity  = $this->addActivity($msg,null,$related_contact_id,null,$this->id,'xepan_communication_viewer&comm_id='.$comm->id,$comm['score']);
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

	function page_deactivate($page){
		ini_set('memory_limit', '-1');
		set_time_limit(0);
		
		$task = $page->add('xepan\projects\Model_Task');
		$task->addCondition('status','<>',"Completed")
		    	 ->addCondition($task->dsql()->orExpr()
				     ->where('assign_to_id',$this->id)
				     ->where($task->dsql()->andExpr()
							      ->where('created_by_id',$this->id)
							      ->where('assign_to_id',null)));
		$total_uncomplete_task = $task->count()->getOne();
		$page->add('H1')->setHtml("<div class='pull-right'> UnComplete Task: ".$total_uncomplete_task."</div>"); 

		$assign_to_emp = $this->add('xepan\hr\Model_Employee');
		$assign_to_emp->addCondition('id','<>',$this->id);
		$assign_to_emp->addCondition('status',"Active");
		$form = $page->add('Form');
		if($total_uncomplete_task){
			$action_field = $form->addField('DropDown','action')
									->setvalueList(['mark_complete'=>'AllTask Mark Complete','reasign'=>'Re-Assign Task to Another Employee'])->setEmptyText('Please Select Action')->validate('required');
			$form->addField('xepan\base\Basic','employee')->setModel($assign_to_emp);						
			$action_field->js(true)->univ()->bindConditionalShow([
				'reasign'=>['employee'],
				'*'=>['action']
			],'div.atk-form-row');
		}
		
		$form->addSubmit('Deactivate Account')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			if($total_uncomplete_task){
				if($form['action']=='mark_complete'){
					foreach ($task as  $complete_task) {
						$new_task =$page->add('xepan\projects\Model_Task');
						$new_task->load($complete_task->id);
						$new_task['status']='Completed';
						$new_task['updated_at']=$this->app->now;
						$new_task['completed_at']=$this->app->now;
						$new_task->save();
						
					}
				}else{
					if(!$form['employee'])
						$form->displayError('employee','Employee Must be select');

					foreach ($task as  $assign_task) {
						$this->add('xepan\projects\Model_Task')
								->load($assign_task->id)
								->set('assign_to_id',$form['employee'])
								->save();
					}
				}
			}

			$my_emails = $this->add('xepan\hr\Model_Post_Email_MyEmails');
			$my_emails->addCondition('id',$this['to_id']);
			$my_emails->tryLoadAny();
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('post_id',$my_emails['post_id']);
			$post_employee=[];
			foreach ($emp as  $employee) {
				$post_employee[] = $employee->id;
			}

			$this['status']='InActive';
			
			$this->app->employee
			->addActivity("Employee :".$this['name']." has been deactivated",$this->id,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
			->notifyTo($post_employee,"Employee :".$this['name']." has been deactivated  by: '".$this->app->employee['name']);
			
			// $this->app->employee
			//             ->addActivity("Employee :".$this['name']." has been deactivated", null Related Document ID,$this->id Related Contact ID,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
			//             ->notifyWhoCan('activate','InActive',$this);
			$this->save();

			if(($user = $this->ref('user_id')) && $user->loaded()) $user->deactivate();
				
			return $this->app->page_action_result = $this->app->js(true,$page->js()->univ()->closeDialog())->univ()->successMessage('Done');				
		}

	}


	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Employee : '".$this['name']."' is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
		if(($user = $this->ref('user_id')) && $user->loaded()) $user->activate();
	}

	function updateSearchString($m){

		if($this->isDirty('post_id')) 
			$this->app->employee_post_id_changed  = $this['post_id'];
		
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

	function getAllowSupportEmail(){
		$my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		$my_email->addCondition('is_active',true);
		$my_email->addCondition('is_support_email',true);
		
		$support_email_array=[];
		foreach ($my_email as $email) {
			$support_email_array[]=$email['id'];
		}
		// var_dump($support_email_array);
		return $support_email_array ;

	}

	function getAllowEmails(){
		$my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		$my_email->addCondition('is_active',true);
		
		$support_email_array=[];
		foreach ($my_email as $email) {
			$support_email_array[]=$email['id'];
		}
		// var_dump($support_email_array);
		return $support_email_array ;

	}

	function countDays($month, $year, $ignore) {
	    $count = 0;
	    $counter = mktime(0, 0, 0, $month, 1, $year);
	    

	    while (date("n", $counter) == $month) {
	    	
	        if (in_array(date("w", $counter), $ignore) == false) {
	            $count++;
	        }
	        $counter = strtotime("+1 day", $counter);
	    }
	    return $count;
	}


	function getOffWeekDay(){
		$week_day_array = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
		$official_off_day = [];
		$week_day_model = $this->add('xepan\base\Model_ConfigJsonModel',
					[
						'fields'=>[
									'monday'=>"checkbox",
									'tuesday'=>"checkbox",
									'wednesday'=>"checkbox",
									'thursday'=>"checkbox",
									'friday'=>"checkbox",
									'saturday'=>"checkbox",
									'sunday'=>"checkbox"
									],
						'config_key'=>'HR_WORKING_WEEK_DAY',
						'application'=>'hr'
					]);
		$week_day_model->tryLoadAny();
		
		foreach ($week_day_array as $day_name => $day_number) {
			// echo "Value = ".$day_name." = number = ".$day_number."<br/>";
			if(!$week_day_model[$day_name])
				$official_off_day[] = $day_number;
		}

		return $official_off_day;
	}

	// official holiday  = oh not included (config offcial off day like sunday) + count of official off day(sunday or saturday)

	function getOfficialHolidays($month,$year,$return_holiday_date_array = false){

		if(isset($this->app->cache[$month][$year]['OfficialHolidays'])){

			if($return_holiday_date_array)
				return $this->app->cache[$month][$year]['OfficialHolidays']['dates'];
			
			return $this->app->cache[$month][$year]['OfficialHolidays']['count'];
		}

		$oh_days = $this->add('xepan\hr\Model_OfficialHoliday',['month'=>$month,'year'=>$year]);

		$official_off_day = $this->getOffWeekDay();

		$total_monthly_working_day = $this->countDays($month, $year, $official_off_day);
		$total_monthly_non_working_day = date('t',strtotime($year.'-'.$month.'-01')) - $total_monthly_working_day;
		
		$req_start_date = date('Y-m-01',strtotime($year.'-'.$month.'-01'));
		$req_end_date = date('Y-m-t',strtotime($year.'-'.$month.'-01'));

		// Get Holidays that are actually crossing this $month & $year
		$oh_days->addCondition('from_date','>=',$req_start_date) // NOT month_from-date
				->addCondition('to_date','<=',$req_end_date);

		$official_holiday_count = 0;
		$official_holiday_date = [];

		foreach ($oh_days as $model) {

			$return_data = $this->app->my_date_diff($model['month_from_date'],$model['month_to_date']);

			for ($i=0; $i <= $return_data['days'] ; $i++) {
				$holiday_date = date('Y-m-d', strtotime($model['month_from_date'] . ' +'.$i.' day'));
				
				// if(!in_array($holiday_date, $official_holiday_date))
				$official_holiday_date[] = $holiday_date;

				$day_sequence = date('w',strtotime($holiday_date));
				if(in_array($day_sequence, $official_off_day))
					continue;

				$official_holiday_count = $official_holiday_count + 1;
			}
		}

		// die();
		$official_holiday_count = $official_holiday_count + $total_monthly_non_working_day;
		
		$this->app->cache[$month][$year]['OfficialHolidays']= [
																'count' => $official_holiday_count,
																'dates' => $official_holiday_date
															];
		if($return_holiday_date_array) return $official_holiday_date;
		return $official_holiday_count;
	}

	function getValueOfTreatHolidayBetweenLeave(){
		if(isset($this->app->cache['TreatHolidayBetweenLeave'])){
			return $this->app->cache['TreatHolidayBetweenLeave'];
		}

		$config_model = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'treat_holiday_between_leave'=>"Line",
								],
					'config_key'=>'HR_HOLIDAY_BETWEEN_LEAVES',
					'application'=>'hr'
				]);
		$config_model->tryLoadAny();

		$value = "AsHoliday";
		if($config_model['treat_holiday_between_leave']) $value = $config_model['treat_holiday_between_leave'];

		$this->app->cache['treat_holiday_between_leave'] = $value;
		return $value;
	}

	function treatLeaveAsHoliday(){
		$value = $this->getValueOfTreatHolidayBetweenLeave();
		if($value == "AsHoliday")
			return true;

		return false;
	}
	function getLeavesOnHoliday($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Leave',['month'=>$month,'year'=>$year]);
		$el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('status','Approved');

		$official_holidays = $this->getOfficialHolidays($month,$year,true);
		$official_off_day = $this->getOffWeekDay();

		//for each employee paid leave
			// for loop for from to to_date
				// if treat leave as holiday
					// if date exists in offcial holiday
						//	continue;
					// if date day number is in offcial off day
						// continue;
			//count ++;

		$pl_count = 0;
		$upl_count = 0;

		foreach ($el_days as $model) {

			// echo "from_date = ".$model['month_from_date']." to date".$model['month_to_date']."<br/>";
			$return_data = $this->app->my_date_diff($model['month_from_date'],$model['month_to_date']);
			for ($i=0; $i <= $return_data['days'] ; $i++) {
				$leave_date = date('Y-m-d', strtotime($model['month_from_date'] . ' +'.$i.' day'));

				// echo $this['name']." = ".$leave_date;
				// echo " Holiday = ".$this->treatLeaveAsHoliday();
				// if($this->treatLeaveAsHoliday()){
					$day_sequence = date('w',strtotime($leave_date));
					if(in_array($leave_date, $official_holidays) OR in_array($day_sequence, $official_off_day)){
						if($model['leave_type'] == "Unpaid")
							$upl_count = $upl_count + 1;

						if($model['leave_type'] == "Paid")
							$pl_count = $pl_count + 1;
					}
				// }
			
			}
		}

		// echo "PL Leaves ".$pl_count."<br/>";
		return ['paid'=>$pl_count,'unpaid'=>$upl_count];
	}
	// paid
	function getPaidLeaves($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Leave',['month'=>$month,'year'=>$year]);
		$el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('leave_type','Paid')
				->addCondition('status','Approved');

		$official_holidays = $this->getOfficialHolidays($month,$year,true);
		$official_off_day = $this->getOffWeekDay();

		//for each employee paid leave
			// for loop for from to to_date
				// if treat leave as holiday
					// if date exists in offcial holiday
						//	continue;
					// if date day number is in offcial off day
						// continue;
			//count ++;

		$pl_count = 0;
		foreach ($el_days as $model) {

			// echo "from_date = ".$model['month_from_date']." to date".$model['month_to_date']."<br/>";
			$return_data = $this->app->my_date_diff($model['month_from_date'],$model['month_to_date']);
			for ($i=0; $i <= $return_data['days'] ; $i++) {
				$leave_date = date('Y-m-d', strtotime($model['month_from_date'] . ' +'.$i.' day'));

				// echo $this['name']." = ".$leave_date;
				// echo " Holiday = ".$this->treatLeaveAsHoliday();
				if($this->treatLeaveAsHoliday()){
					if(in_array($leave_date, $official_holidays)) continue;

					$day_sequence = date('w',strtotime($leave_date));

					if(in_array($day_sequence, $official_off_day)) continue;
				}
				$pl_count = $pl_count + 1;
			}
		}

		// echo "PL Leaves ".$pl_count."<br/>";
		return $pl_count;
	}

	function getUnPaidLeaves($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Leave',['month'=>$month,'year'=>$year]);
		$el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('leave_type','Unpaid')
				->addCondition('status','Approved');

		$official_holidays = $this->getOfficialHolidays($month,$year,true);
		$official_off_day = $this->getOffWeekDay();
		
		$upl_count = 0;
		foreach ($el_days as $model) {
			
			$return_data = $this->app->my_date_diff($model['month_from_date'],$model['month_to_date']);

			for ($i=0; $i <= $return_data['days'] ; $i++) {
				$leave_date = date('Y-m-d', strtotime($model['month_from_date'] . ' +'.$i.' day'));

				if($this->treatLeaveAsHoliday()){
					if(in_array($leave_date, $official_holidays)) continue;

					$day_sequence = date('w',strtotime($leave_date));

					if(in_array($day_sequence, $official_off_day)) continue;
				}

				$upl_count = $upl_count + 1;
			}

		}

		return $upl_count;	
	}

	function getPresent($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Attandance');
		$el_days->addExpression('month','MONTH(from_date)');
		$el_days->addExpression('year','YEAR(from_date)');

		return $el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition([['is_holiday',false],['is_holiday',null]])
				->addCondition('year',$year)
				->sum('working_unit_count')->getOne();
	}

	function getExtraWorking($month,$year){

		$startdate = date('Y-m-01',strtotime($year."-".$month."-01"));
		$enddate = date('Y-m-t',strtotime($startdate));
		
		$el_days = $this->add('xepan\hr\Model_Employee_Attandance',['from_date'=>$startdate,'to_date'=>$enddate]);
		$el_days->addExpression('month','MONTH(from_date)');
		$el_days->addExpression('year','YEAR(from_date)');

		$el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('is_holiday',true);

		$data = [];
		$data['days'] = $el_days->sum('working_unit_count')->getOne();
		$data['hours'] = $el_days->sum('holidays_extra_work_hours')->getOne();
		return $data;
	}

	function getPresenceInTime($date,$emp_id,$present_value,$present_type){

		$emp_m = $this->add('xepan\hr\Model_Employee');
		$emp_m->addExpression('official_date')->set('DATE("'.$date.'")');

		$emp_m->addExpression('official_day_start')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('official_date'),
					$m->getElement('in_time')
				]);
		});

		$emp_m->load($emp_id);
		
		return $emp_m['official_day_start'];
		
	}

	function getPresenceOutTime($date,$emp_id,$present_value,$present_type){

		$emp_m = $this->add('xepan\hr\Model_Employee');
		$emp_m->addExpression('official_date')->set('DATE("'.$date.'")');

		$emp_m->addExpression('official_day_end')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('official_date'),
					$m->getElement('out_time')
				]);
		});

		$emp_m->load($emp_id);
		
		return $emp_m['official_day_end'];
		
	}

	function getSalarySlip($month,$year,$salary_sheet_id,$TotalWorkDays){

		$TotalMonthDays = date('t',strtotime($year.'-'.$month.'-01'));
		$OfficialHolidays = $this->getOfficialHolidays($month,$year);
		
		// $TotalWorkDays = $TotalMonthDays - $OfficialHolidays;
		$PaidLeaves = $this->getPaidLeaves($month,$year);
		$UnPaidLeaves = $this->getUnPaidLeaves($month,$year);
		$Present = $this->getPresent($month,$year);
		$extraWorking = $this->getExtraWorking($month,$year);
		$leave_on_holiday = $this->getLeavesOnHoliday($month,$year);

		if(!$this->treatLeaveAsHoliday())
			$OfficialHolidays -= ($leave_on_holiday['paid'] + $leave_on_holiday['unpaid']); 

		$calculated = [
				'TotalWorkingDays'=>$TotalWorkDays,
				'PaidLeaves'=>$PaidLeaves,
				'UnPaidLeaves'=>$UnPaidLeaves,
				'Presents'=>$Present,
				'OfficialHolidays'=>$OfficialHolidays,
				'ExtraWorkingDays'=>$extraWorking['days'],
				'ExtraWorkingHours'=>$extraWorking['hours'],
				'PaidLeavesOnHoliday'=>$leave_on_holiday['paid'],
				'UnPaidLeavesOnHoliday'=>$leave_on_holiday['unpaid'],
				'PaidDays'=>$Present + $PaidLeaves + $OfficialHolidays,
				'Absents'=>$TotalWorkDays - ($Present + $PaidLeaves + $OfficialHolidays + $UnPaidLeaves),
			];

		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_reimbursement_affect_salary'=>"Line",
						],
			'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$reimbursement_config_model->tryLoadAny();

		
		if($reimbursement_config_model['is_reimbursement_affect_salary'] === "yes"){
			$calculated['Reimbursement'] = $this->getReimbursement($month,$year);
		}


		$deduction_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'is_deduction_affect_salary'=>"Line",
						],
			'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
			'application'=>'hr'
		]);
		$deduction_config_model->tryLoadAny();

		if($deduction_config_model['is_deduction_affect_salary'] === "yes")
			$calculated['Deduction']= $this->getDeduction($month,$year);


		$net_amount = 0;

		foreach ($this->ref('EmployeeSalary') as $salary) {

			$result = $this->evalSalary($salary['amount'],$calculated);
			
			$salary['salary'] = preg_replace('/\s+/', '',$salary['salary']);

			$calculated[$salary['salary']] = $result;
			if($salary['add_deduction'] == "add"){
				$net_amount += $result;
			}
			if($salary['add_deduction'] == "deduction")
				$net_amount -= $result;
		}

		$calculated['NetAmount'] = $net_amount;

		if(!$salary_sheet_id) return ['calculated'=>$calculated,'loaded'=>[]];

		$existing_m= $this->add('xepan\hr\Model_EmployeeRow');
		$existing_m->addCondition('salary_abstract_id',$salary_sheet_id);
		$existing_m->addCondition('employee_id',$this->id);
		$existing_m->tryLoadAny();
		
		if(!$existing_m->loaded()){			
			return ['calculated'=>$calculated,'loaded'=>[]];
		} 

		$loaded = [
				'TotalWorkingDays'=>$existing_m['total_working_days'],
				'Presents'=>$existing_m['presents'],
				'PaidLeaves'=>$existing_m['paid_leaves'],
				'UnPaidLeavs'=>$existing_m['unpaid_leaves'],
				'Absents'=>$existing_m['absents'],
				'OfficialHolidays'=>$OfficialHolidays,
				'Reimbursement'=>$existing_m['reimbursement_amount'],
				'Deduction'=>$existing_m['deduction_amount'],
				'PaidDays'=>$existing_m['paiddays'],
				'NetAmount'=>$existing_m['net_amount']
			];

		$sal = $this->add('xepan\hr\Model_Salary');
		foreach ($sal->getRows() as &$s) {
			$temp = preg_replace('/\s+/', '',$s['name']);
			$loaded[$temp] = $existing_m[$this->app->normalizeName($s['name'])];
		}

		$return_array = [
			'calculated'=>$calculated,
			'loaded'=>$loaded
		];

		return $return_array;
	}

	// @expression = {Base} * 12 /100
	// base_array = $calculated_array .. updated in each loop in getSalarySlip function
	function evalSalary($expression, $base_array){
		// solve expression by base array
		// var_dump($expression);
		// var_dump($base_array);
		// echo "<br/>";

		if(!$expression) return 0;
		$expression = preg_replace('/\s+/', '',$expression);

		foreach ($base_array as $key => $value){
			$key = preg_replace('/\s+/', '',$key);
			$value = $value?$value:0;
			$expression = str_replace("{".$key."}", $value, $expression);
		}

		if(strpos($expression, "{") !== false OR strpos($expression, "}") !== false)
			throw new \Exception("please correct salary (".$expression.") of employee ".$this['name']);

		eval('$return = '.$expression.';');
		// echo "returning ".$return . " for expression '$expression' <br/>";
		return $return;

		// implmenet min and max functions 
		$m = new \xepan\hr\EvalMath;
		return $result = $m->evaluate($expression);
	}

	/*
	* @return = ['salary_id'=>expression]
	*/
	function getApplySalary(){
		if(!$this->loaded()) throw new \Exception("model must loaded", 1);
		
		$emp_salary = $this->add('xepan\hr\Model_Employee_Salary')
					->addCondition('employee_id',$this->id)
					->getRows();
		$result_array = [];
		foreach ($emp_salary as $key => $salary_info) {
			$result_array[$salary_info['salary_id']] = [
														'expression'=>$salary_info['amount']?:0,
														'add_deduction'=>$salary_info['add_deduction']
													];
		}

		return $result_array;
	}

	function getReimbursement($month,$year){
		
		if(!$this->loaded()) throw new \Exception("model must loaded", 1);
		
		$reimburs_mdl = $this->add('xepan\hr\Model_Reimbursement');
		$reimburs_mdl->addCondition('employee_id',$this->id)
					->addCondition([['status',"Approved"],['status',"PartiallyPaid"]]);
		
		$l_date = date('Y-m-t',strtotime($year.'-'.$month.'-01'));

		$total_amount = 0;
		foreach ($reimburs_mdl as $rd) {
			$reimburs_mdl_detail = $this->add('xepan\hr\Model_ReimbursementDetail')
									->addCondition('reimbursement_id',$rd->id)
									->addCondition('date','<=',$l_date);

			foreach ($reimburs_mdl_detail as $amount_mdl) {
				$count = $amount_mdl->count()->getOne();
					for ($i=0; $i < $count ; $i++) {
						$amount = 0;
						$amount = $amount_mdl['due_amount'];
					}
				$total_amount += $amount;
			}		
		}
		return $total_amount;
	}

	function getDeduction($month,$year){
		if(!$this->loaded()) throw new \Exception("model must loaded", 1);
		
		$last_month_date =  date('Y-m-t',strtotime($year.'-'.$month.'-01'));
		
		$total_amount = 0;
		$deduction_mdl = $this->add('xepan\hr\Model_Deduction')
					->addCondition('employee_id',$this->id)
					->addCondition([['status','Approved'],['status','PartiallyRecieved']])
					->addCondition([['created_at','<=',$last_month_date],['updated_at','<=',$last_month_date]]);
		
		foreach ($deduction_mdl as $amount_mdl) {

			$count = $amount_mdl->count()->getOne();
				for ($i=0; $i < $count ; $i++) {
					$amount = 0;
					$amount = $amount_mdl['due_amount'];
				}
			$total_amount += $amount;
		}		
		
		return $total_amount;
	}

	function ledger(){
		$account = $this->add('xepan\accounts\Model_Ledger')
				->addCondition('contact_id',$this->id)
				->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->load("Sundry Creditor")->get('id'));
		$account->tryLoadAny();
		if(!$account->loaded()){
			$account['name'] = $this['unique_name'];
			$account['LedgerDisplayName'] = $this['unique_name'];
			$account['ledger_type'] = 'Employee';
			$account->save();
		}else{
			if($account['name'] != $this['unique_name']){
				$account['name'] = $this['unique_name'];
				$account['updated_at'] = $this->app->now;
				$account->save();
			}
		}

		return $account;

	}

	function userLoggedout($app,$auth_model){
		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		
		$movement->addCondition('employee_id',$this->app->employee->id);
		$movement->addCondition('movement_at',$this->app->now);
		$movement->addCondition('direction','Out');
		$movement->save();

		$attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
		$attan_m->addCondition('employee_id',$this->app->employee->id);
		$attan_m->addCondition('fdate',$this->app->today);
		$attan_m->setOrder('id','desc');
		$attan_m->tryLoadAny();

		if($attan_m->loaded()){
		// initially it was considered that official outing is not actually outing, you are working
		// for office but just out of premises... then changed.. if it is so .. just don't log out
		// and make task what you are doing where ??

		// if($movement['reason'] != 'Official Outing'){
			$attan_m['to_date'] = $this->app->now;
			$attan_m['total_movement_out'] = $attan_m['total_movement_out'] + 1;
			$attan_m->save();
		// }
		}
				
	}


	function getAvailableLeave($on_date,$leave_id){

		$leaveallowed_model = $this->add('xepan\hr\Model_Employee_LeaveAllow');
		$leaveallowed_model->addCondition('employee_id',$this->id);
		$leaveallowed_model->addCondition('leave_id',$leave_id);
		$leaveallowed_model->tryLoadAny();

		if(!$leaveallowed_model->loaded()) return 0;


		$startdate =  date('Y-m-01',strtotime($leaveallowed_model['effective_date']));
		$enddate =  date('Y-m-t',strtotime($on_date));

		$date_diff = $this->app->my_date_diff($startdate,$enddate);
		$month_total = $date_diff['months_total'] + ($date_diff['days']?1:0);
		$year_total = $date_diff['years'] + ($date_diff['months']?1:0);

		$total_leaves = $leaveallowed_model['no_of_leave'];

		if($leaveallowed_model['is_unit_carried_forward']){
			switch ($leaveallowed_model['unit']) {
				case 'Monthly':	
					$total_leaves = $total_leaves * $month_total;
					break;
				case 'Yearly':	
					$total_leaves = $total_leaves * $year_total;
					break;
				
			}
		}
		$total_leaves += $leaveallowed_model['previously_carried_leaves']?:0;

		$leave_taken = $this->add('xepan\hr\Model_Employee_Leave')
						->addCondition('emp_leave_allow_id',$leaveallowed_model->id)
						->addCondition('employee_id',$this->id)
						->addCondition('from_date','>=',$leaveallowed_model['effective_date'])
						->addCondition('status','Approved')
						->sum('no_of_leave')->getOne();
		return ($total_leaves - $leave_taken);
	}


}
