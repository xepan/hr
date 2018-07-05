<?php

namespace xepan\hr;

class Model_Post extends \xepan\hr\Model_Document{

	public $status=['Active','InActive'];
	public $actions = [
						'Active'=>['view','edit','update_employee_leaveTemplate','update_employee_salaryTemplate','associateEmail','deactivate'],
						'InActive' => ['view','edit','update_employee_leaveTemplate','update_employee_salaryTemplate','delete','activate']
					];

	public $title_field = "name_with_dept";
	function init(){
		parent::init();

		$post_j = $this->join('post.document_id');
		
		$post_j->hasOne('xepan\hr\Department','department_id')->sortable(true);
		$post_j->hasOne('xepan\hr\ParentPost','parent_post_id')->sortable(true);
		$post_j->hasOne('xepan\hr\SalaryTemplate','salary_template_id');
		$post_j->hasOne('xepan\hr\LeaveTemplate','leave_template_id');

		$post_j->addField('name')->sortable(true);
		$post_j->addField('order')->type('number')->sortable(true);
		$post_j->addField('allowed_menus')->display(['form'=>'xepan\base\NoValidateDropDown']);
		
		$post_j->addField('in_time')->display(array('form' => 'TimePicker'));
		$post_j->addField('out_time')->display(array('form' => 'TimePicker'));
		$post_j->addField('permission_level')->enum(['Individual','Sibling','Department','Global'])->defaultValue('Individual');
		$post_j->addField('finacial_permit_limit');
		
		$post_j->hasMany('xepan\hr\Post','parent_post_id',null,'ParentPosts');
		$post_j->hasMany('xepan\hr\Post_Email_Association','post_id',null,'EmailPermissions');
		$post_j->hasMany('xepan\hr\Employee','post_id',null,'Employees');

		$this->addExpression('all_employee_count')->set($this->refSQL('Employees')->count())->sortable(true);
		$this->addExpression('employee_count')->set($this->refSQL('Employees')->addCondition('status','Active')->count())->sortable(true);
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Post');

		$this->addHook('beforeSave',[$this,'changeEmployeeInOutTimes']);
		$this->addHook('beforeSave',[$this,'validateInOutTime']);
		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeDelete',[$this,'deleteEmailAssociation']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);

		$this->addExpression('name_with_dept')
			->set($this->dsql()->expr('CONCAT([0]," :: ",[1])',
				[
				$this->getElement('department'),$this->getElement('name')]))->sortable(true);

		$this->is([
			'department_id|required'
			]);

	}

	function validateInOutTime(){
		if(strtotime($this['out_time']) <= strtotime($this['in_time']))
			throw $this->exception('In Time must be smaller then out time','ValidityCheck')->setField('in_time');
	}

	function descendantPosts($include_self = true){		
		if(!$this->loaded()) throw $this->exception('PLease call on loaded model');

		$descendants = [];

		if($include_self)
			$descendants[] = $this->id;

		// return $descendants;

		$sub_posts = $this->add('xepan\hr\Model_Post');
		$sub_posts->addCondition('parent_post_id',$this->id);
		$sub_posts->addCondition('id','<>',$this->id);
		
		foreach ($sub_posts as $sub_post){
			$descendants = array_merge($descendants, $sub_post->descendantPosts(true));
		}

		return $descendants;
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Post : '".$this['name']."'  now active, related to Department : '".$this['department']."' ", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity(" Post : '".$this['name']."' has been deactivated, related to Department : '".$this['department']."' ", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->saveAndUnLoad();
	}

	function page_update_employee_leaveTemplate($page){
		//post wise employees par leave update karni h
		// action click pr page open hoga
		// available leave update karenge
		// form 
			// post employee he 
			//
		// submit 
			// update karena 

		$page->add('View')->set("Leave Template : ".$this['leave_template'] );
		$form = $page->add('Form');

		$form->addField('Checkbox','select_all_employee')->set(1);
		$multiselect_field = $form->addField('dropDown','employee');
		$multiselect_field->addClass('multiselect-full-width')
					->setAttr(['multiple'=>'multiple']);
		$multiselect_field->setModel($this->employee());

		$form->addSubmit('Update');

		if($form->isSubmitted()){
			
			$emp_model = $this->add('xepan\hr\Model_Employee');
			$emp_model->addCondition('post_id',$this->id);
			if(!$form['select_all_employee'] AND $form['employee'])
				$emp_model->addCondition('id',explode(",", $form['employee']));
			
			// foreach loop for employee 
			foreach ($emp_model as $emp) {
				$emp->updateEmployeeLeave($force_update = 1);
			}
			$this->app->page_action_result = $form->js(null,$form->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Leave Template Update');
		}

	}

	function employee(){
		$ass_emp = $this->add('xepan\hr\Model_Employee');
		$ass_emp->addCondition('post_id',$this->id);
		return $ass_emp;
	}

	function page_update_employee_salaryTemplate($page){
		// add view
		// add form
			// post employee
				// submit
			// update on
				// each employee
		$page->add('View')->set("Salary Template : ".$this['salary_template']);
		$form = $page->add('Form');

		$form->addField('checkBox','select_all_employees')->set(1);
		$multiselect_field = $form->addField('dropDown','employee');
		$multiselect_field->addClass('multiselect-full-width')
					->setAttr(['multiple'=>'multiple']);
		$multiselect_field->setModel($this->employee());

		$form->addSubmit('Update');

		if($form->isSubmitted()){

			$emp_model = $this->add('xepan\hr\Model_Employee');
			$emp_model->addCondition('post_id',$this->id);

			if(!$form['select_all_employee'] AND $form['employee'])
				$emp_model->addCondition('id',explode(",", $form['employee']));

			
			foreach ($emp_model as $emp) {				
				$emp->updateEmployeeSalary($force_update = 1);
			}
			$this->app->page_action_result = $form->js(null,$form->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Salary Template Update');

		}
	}

	function page_associateEmail($page){
		$page->add('View')->set('Associate Email');
		$form = $page->add('Form');

		$ass_email = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$ass_email->addCondition('is_active',true);
		$col = $form->add('Columns')->addClass('row xepan-push');
		foreach ($ass_email as $emails) {
			$asso_email = $this->add('xepan\hr\Model_Post_Email_Association');
			$asso_email->addCondition('post_id',$this->id);
			$asso_email->addCondition('emailsetting_id',$emails->id);
			$asso_email->tryLoadAny();	
			$col1= $col->addColumn(3)->addClass('col-md-3');
			$email_field = $col1->addField('Checkbox','allow_email_'.$emails->id,$emails['name']);
			if($asso_email->loaded())
				$email_field->set(true);
		}

		$form->addSubmit('Associate Email')->addClass('btn btn-success');

		if($form->isSubmitted()){
			$emails_added=[];
			$this->ref('EmailPermissions')->deleteAll();
			foreach ($ass_email as $emails) {
				if($form['allow_email_'.$emails->id]){
					$asso_email = $this->add('xepan\hr\Model_Post_Email_Association');
					$asso_email->addCondition('post_id',$this->id);
					$asso_email->addCondition('emailsetting_id',$emails->id);
					$asso_email->tryLoadAny();
					
					if(!$asso_email->loaded()){
						$asso_email['post_id'] =  $this->id;
						$asso_email['emailsetting_id'] =  $emails->id;
						$asso_email->save();
					}
					$emails_added[] = $emails['name'];
				}
			}
			$email_string = (implode(", ", $emails_added));
			$this->app->employee
			    ->addActivity("These Emails : '".$email_string."' associated with this Post : '".$this['name']."'", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
				->notifyWhoCan(' ',' ',$this);
			$this->app->page_action_result = $form->js(null,$form->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Associate Emails SuccessFully');

		}

	}

	function beforeDelete(){
		if($this->ref('Employees')->count()->getOne())
			throw new \Exception("Can not Delete Content First delete Employees", 1);
	}
	function deleteEmailAssociation(){
		$this->ref('EmailPermissions')->deleteAll();
	}

	function changeEmployeeInOutTimes(){
		$model_employee = $this->add('xepan\hr\Model_Employee')->addCondition('post_id',$this->id);
		
		if($this->dirty['in_time']){
			foreach ($model_employee as $emp) {
				$emp['in_time'] = $this['in_time'];
				$emp->save();
			}
		}

		if($this->dirty['out_time']){
			foreach ($model_employee as $emp) {
				$emp['out_time'] = $this['out_time'];
				$emp->save();
			}
		}
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['name'];
		$search_string .=" ".$this['in_time'];
		$search_string .=" ".$this['out_time'];


		if($this->loaded()){
			$parent_post = $this->ref('ParentPosts');
			foreach ($parent_post as $parent_post_detail) 
			{
				$search_string .=" ". $parent_post_detail['name'];
				$search_string .=" ". $parent_post_detail['in_time'];
				$search_string .=" ". $parent_post_detail['out_time'];
			}
		}

		if($this->loaded()){
			$employees = $this->ref('Employees');
			foreach ($employees as $employees_detail) {
				$search_string .=" ". $employees_detail['contract_date'];
				$search_string .=" ". $employees_detail['doj'];
			}
		}

		$this['search_string'] = $search_string;
	}

	function associatedEmailSettings(){
		$asso_email = $this->add('xepan\hr\Model_Post_Email_Association');
		$asso_email->addCondition('post_id',$this->id);

		$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$email_settings->addCondition('id','in',$asso_email->fieldQuery('emailsetting_id'));

		return $email_settings;
	}
}
