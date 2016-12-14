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

		$emp_j->hasOne('xepan\base\GraphicalReport','graphical_report_id');
		
		$emp_j->hasOne('xepan\hr\Department','department_id')->sortable(true)->display(array('form' => 'xepan\commerce\DropDown'));
		$emp_j->hasOne('xepan\hr\Post','post_id')->display(array('form' => 'xepan\commerce\DropDown'));
		
		$emp_j->addField('notified_till')->type('number')->defaultValue(0); // TODO Should be current id of Activity
		$emp_j->addField('offer_date')->type('date')->sortable(true);
		$emp_j->addField('doj')->caption('Date of Joining')->type('date')->defaultValue(@$this->app->now)->sortable(true);
		$emp_j->addField('contract_date')->type('date');
		$emp_j->addField('leaving_date')->type('date');
		$emp_j->addField('attandance_mode')->enum(['Web Login','Mannual'])->defaultValue('Web Login');
		$emp_j->addField('in_time')->display(array('form' => 'TimePicker'));
		$emp_j->addField('out_time')->display(array('form' => 'TimePicker'));

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

		$this->addExpression('check_login')->set(function($m,$q){
			 $attan_m = $this->add("xepan\hr\Model_Employee_Attandance")
						->addCondition('employee_id',$m->getElement('id'))
						->addCondition('fdate',$this->app->today);

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
		$this->addHook('afterSave',[$this,'updateEmployeeSalary']);
		$this->addHook('afterSave',[$this,'updateEmployeeLeave']);
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

	function updateEmployeeSalary(){

		if(isset($this->app->employee_post_id_changed)){
			
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
			}
		}
	}

	function updateEmployeeLeave(){
		
		if(isset($this->app->employee_post_id_changed)){
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

	// function updateTemplates(){
	// 	// copy salary and leave templates of posts
	// 	$post = $this->add('xepan\hr\Model_Post')->tryLoadBy('id',$this['post_id']);
		
	// 	if(!$post->loaded())
	// 		return;
		
	// 		$this['salary_template_id'] = $post['salary_template_id'];  
	// 		$this->save(); 
	// }

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
			$attan_m['is_holiday']  = $attan_m->isHoliday($attan_m['fdate']);
		}/*else{
			$attan_m['to_date']  = null;
		}*/
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
            ->addActivity("Employee : '".$this['name']."' has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this->id."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
		if(($user = $this->ref('user_id')) && $user->loaded()) $user->deactivate();
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

	function getOfficialHolidays($month,$year){
		if(isset($this->app->cache[$month][$year]['OfficialHolidays'])) return $this->app->cache[$month][$year]['OfficialHolidays'];
		$oh_days = $this->add('xepan\hr\Model_OfficialHoliday');
		//	getWeekdays off from config in terms of array 0 => sunday 1= Monday
		$week_day_array = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
		$ignore_array = [];
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

		foreach ($week_day_model as $day=>$value) {
			if(!$value)
				$ignore_array[] = $week_day_array[$day];
		}

		$wekklyOff = $this->countDays($month, $year, $ignore_array);

		$h_c=  $oh_days->addCondition('month',$month)
				->addCondition('year',$year)
				->sum($this->dsql()->expr('IFNULL([0],0)+[1]',[$oh_days->getElement('month_holidays'),$wekklyOff]))
				->getOne()
				;
		$this->app->cache[$month][$year]['OfficialHolidays']= $h_c;
		return $h_c;
	}

	function getPaidLeaves($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Leave');
		return $el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('leave_type','Paid')
				->addCondition('status','Approved')
				->sum($this->dsql()->expr('IFNULL([0],0)',[$el_days->getElement('month_leaves')]))
				->getOne();
	}

	function getUnPaidLeaves($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Leave');
		return $el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('year',$year)
				->addCondition('leave_type','Unpaid')
				->addCondition('status','Approved')
				->sum($this->dsql()->expr('IFNULL([0],0)',[$el_days->getElement('month_leaves')]))
				->getOne();
		
	}

	function getPresent($month,$year){

		$el_days = $this->add('xepan\hr\Model_Employee_Attandance');
		$el_days->addExpression('month','MONTH(from_date)');
		$el_days->addExpression('year','YEAR(from_date)');

		return $el_days
				->addCondition('employee_id',$this->id)
				->addCondition('month',$month)
				->addCondition('is_holiday',false)
				->addCondition('year',$year)
				->count()->getOne();
	}

	
	// echo countDays(2013, 1, array(0, 6)); // 23

	function getSalarySlip($month,$year,$salary_sheet_id,$TotalWorkDays){

		$TotalMonthDays = date('t',strtotime($year.'-'.$month.'-01'));
		$OfficialHolidays = $this->getOfficialHolidays($month,$year);
		
		// $TotalWorkDays = $TotalMonthDays - $OfficialHolidays;

		$PaidLeaves = $this->getPaidLeaves($month,$year);
		$UnPaidLeaves = $this->getUnPaidLeaves($month,$year);
		$Present = $this->getPresent($month,$year);

		$calculated = [
				'TotalWorkingDays'=>$TotalWorkDays,
				'PaidLeaves'=>$PaidLeaves,
				'UnPaidLeaves'=>$UnPaidLeaves,
				'Presents'=>$Present,
				'PaidDays'=>$Present + $PaidLeaves,
				'Absents'=>$TotalWorkDays - ($Present + $PaidLeaves),
			];
		$net_amount = 0;
		foreach ($this->ref('EmployeeSalary') as $salary) {
			$result = $this->evalSalary($salary['amount'],$calculated);
			
			$salary['salary'] = preg_replace('/\s+/', '',$salary['salary']);

			$calculated[$salary['salary']] = $result;

			if($salary['add_deduction'] == "add")
				$net_amount += $result;
			if($salary['add_deduction'] == "deduction")
				$net_amount -= $result;
		}

		$calculated['NetAmount'] = $net_amount;

		if(!$salary_sheet_id) return ['calculated'=>$calculated,'loaded'=>[]];

		$existing_m= $this->add('xepan\hr\Model_EmployeeRow');
		$existing_m->addCondition('salary_abstract_id',$salary_sheet_id);
		$existing_m->addCondition('employee_id',$this->id);
		$existing_m->tryLoadAny();
		if(!$existing_m->loaded()) return ['calculated'=>$calculated,'loaded'=>[]];

		$loaded = [
				'TotalWorkingDays'=>$existing_m['total_working_days'],
				'Presents'=>$existing_m['presents'],
				'PaidLeaves'=>$existing_m['paid_leaves'],
				'UnPaidLeavs'=>$existing_m['unpaid_leaves'],
				'Absents'=>$existing_m['absents'],
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

		// echo "<pre>";
		// print_r($return_array);

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

		foreach ($base_array as $key => $value) {
			$key = preg_replace('/\s+/', '',$key);
			$expression = str_replace("{".$key."}", $value, $expression);
		}
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
		
		$emp_salary = $this->add('xepan\hr\Model_Employee_Salary')->addCondition('employee_id',$this->id)->getRows();
		$result_array = [];
		foreach ($emp_salary as $key => $salary_info) {
			$result_array[$salary_info['salary_id']] = [
														'expression'=>$salary_info['amount']?:0,
														'add_deduction'=>$salary_info['add_deduction']
													];
		}

		return $result_array;
	}

}
