<?php

namespace xepan\hr;

class Model_Post extends \xepan\hr\Model_Document{

	public $status=['Active','InActive'];
	public $actions = [
						'Active'=>['view','edit','associateEmail','deactivate'],
						'InActive' => ['view','edit','delete','activate']
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
		$post_j->addField('in_time')->display(array('form' => 'TimePicker'));
		
		$post_j->addField('out_time')->display(array('form' => 'TimePicker'));

		$post_j->hasMany('xepan\hr\Post','parent_post_id',null,'ParentPosts');
		$post_j->hasMany('xepan\hr\Post_Email_Association','post_id',null,'EmailPermissions');
		$post_j->hasMany('xepan\hr\Employee','post_id',null,'Employees');

		$this->addExpression('employee_count')->set($this->refSQL('Employees')->count())->sortable(true);
		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Post');

		$this->addHook('beforeSave',[$this,'changeEmployeeInOutTimes']);
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
	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("'".$this['name']."' post  is now active of department '".$this['department']."' ", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("'".$this['name']."' post has been deactivated in '".$this['department']."' department ", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->saveAndUnLoad();
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
				}
			}
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
}
