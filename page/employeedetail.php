<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_employeedetail extends \xepan\base\Page {
	public $title='Employee Details';
	public $employee;
	public $breadcrumb=['Home'=>'index','Employee'=>'xepan_hr_employee','Detail'=>'#'];

	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';

		$this->employee = $employee= $this->add('xepan\hr\Model_Employee')->tryLoadBy('id',$this->api->stickyGET('contact_id'));

		if($action=="add"){
			$this->template->tryDel('details');

			$base_validator = $this->add('xepan\base\Controller_Validator');

			$form = $this->add('Form',['validator'=>$base_validator],'contact_view_full_width');
			$form->setLayout(['page/employeeprofile-compact']);			
			$form->setModel($employee,['graphical_report_id','branch_id','first_name','last_name','address','city','country_id','state_id','pin_code','organization','post_id','website','remark','department_id']);
			$form->addField('line','email_1')->validate('email');
			$form->addField('line','email_2');
			$form->addField('line','email_3');
			$form->addField('line','email_4');
			$emails_allowed_field = $form->addField('DropDown','permitted_emails')->setEmptyText('As Per Post Only')->enableMultiSelect();
			$emails_allowed_field->setModel('xepan\communication\Model_Communication_EmailSetting');
			$emails_allowed_field = $form->addField('Checkbox','allow_login_from_anywhere');
			
			$dept_field = $form->getElement('department_id');
			$post_field = $form->getElement('post_id');
			$country_field =  $form->getElement('country_id');
			$country_field->getModel()->addCondition('status','Active');
			$state_field = $form->getElement('state_id');
			$state_field->getModel()->addCondition('status','Active')->addCondition('country_status','Active');

			if($dept_id = $this->app->stickyGET('dept_id')){			
				$post_field->getModel()->addCondition('department_id',$dept_id);
			}
		
			// if($cntry_id = $this->app->stickyGET('country_id')){			
			// 	$state_field->getModel()->addCondition('country_id',$cntry_id);
			// }
			$state_field->dependsOn($country_field);

			$dept_field->js('change',$post_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$post_field->name]),'dept_id'=>$dept_field->js()->val()]));
			// $country_field->js('change',$state_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$state_field->name]),'country_id'=>$country_field->js()->val()]));

			$form->addField('line','contact_no_1');
			$form->addField('line','contact_no_2');
			$form->addField('line','contact_no_3');
			$form->addField('line','contact_no_4');
			$form->addField('Checkbox','want_to_add_next_employee')->set(true);

			$form->addField('line','user_id')->validate('email');
			$form->addField('password','password');
			
			// add other info
			$this->employee->addOtherInfoToForm($form);

			$form->addSubmit('Add');
			if($form->isSubmitted()){			
				try{
					$this->api->db->beginTransaction();
					$form->save();
					$new_employee_model = $form->getModel();

					if($form['user_id'] && $form['password']){
						$user = $this->add('xepan\base\Model_User');
						$user->addCondition('scope','AdminUser');
						$user->addCondition('username',$form['user_id']);
						$user->tryLoadAny();

						if($user->loaded())
							$form->displayError('user_id','username already exist');
						// $user=$this->add('xepan\base\Model_User');
						$this->add('BasicAuth')
						->usePasswordEncryption('md5')
						->addEncryptionHook($user);
						
						$user['username'] = $form['user_id'];
						$user['password'] = $form['password'];
						$user->save();
						
						$new_employee_model['user_id'] = $user->id;
						$new_employee_model->save();
					}

					if($form['post_id']){
						$post = $this->add('xepan\hr\Model_Post');
						$post->tryload($form['post_id']);
						$new_employee_model['in_time'] = $post['in_time'];
						$new_employee_model['out_time'] = $post['out_time'];
						$new_employee_model->save();
					}

					if($form['email_1']){
						$new_employee_model->checkEmail($form['email_1'],null,'email_1');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_employee_model->id;
						$email['head'] = "Official";
						$email['value'] = trim($form['email_1']);
						$email->save();
					}

					if($form['email_2']){
						$new_employee_model->checkEmail($form['email_2'],null,'email_2');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_employee_model->id;
						$email['head'] = "Official";
						$email['value'] = trim($form['email_2']);
						$email->save();
					}

					if($form['email_3']){
						$new_employee_model->checkEmail($form['email_3'],null,'email_3');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_employee_model->id;
						$email['head'] = "Personal";
						$email['value'] = trim($form['email_3']);
						$email->save();
					}
					if($form['email_4']){
						$new_employee_model->checkEmail($form['email_4'],null,'email_4');

						$email = $this->add('xepan\base\Model_Contact_Email');
						$email['contact_id'] = $new_employee_model->id;
						$email['head'] = "Personal";
						$email['value'] = trim($form['email_4']);
						$email->save();
					}

					// Contact Form
					if($form['contact_no_1']){

						$new_employee_model->checkPhone($form['contact_no_1'],null,'contact_no_1');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_employee_model->id;
						$phone['head'] = "Official";
						$phone['value'] = $form['contact_no_1'];
						$phone->save();
					}

					if($form['contact_no_2']){
						$new_employee_model->checkPhone($form['contact_no_2'],null,'contact_no_2');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_employee_model->id;
						$phone['head'] = "Official";
						$phone['value'] = $form['contact_no_2'];
						$phone->save();
					}

					if($form['contact_no_3']){
						$new_employee_model->checkPhone($form['contact_no_3'],null,'contact_no_3');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_employee_model->id;
						$phone['head'] = "Personal";
						$phone['value'] = $form['contact_no_3'];
						$phone->save();
					}
					if($form['contact_no_4']){
						$new_employee_model->checkPhone($form['contact_no_4'],null,'contact_no_4');

						$phone = $this->add('xepan\base\Model_Contact_Phone');
						$phone['contact_id'] = $new_employee_model->id;
						$phone['head'] = "Personal";
						$phone['value'] = $form['contact_no_4'];
						$phone->save();
					}				

					if($form['permitted_emails']){
						foreach (explode(",",$form['permitted_emails']) as $email_setting_id) {
							$this->add('xepan\hr\Model_Post_Email_Association')
								->set('employee_id',$new_employee_model->id)
								->set('emailsetting_id',$email_setting_id)
								->save();
						}
						
					}					

					// add contact other info
					$contact_other_info_config_m = $this->add('xepan\base\Model_Config_ContactOtherInfo');
					$contact_other_info_config_m->addCondition('for','Employee');

					foreach($contact_other_info_config_m->config_data as $of) {
						if($of['for'] != "Employee" ) continue;

						if(!$of['name']) continue;
						$field_name = $this->app->normalizeName($of['name']);

						$existing = $this->add('xepan\base\Model_Contact_Other')
							->addCondition('contact_id',$new_employee_model->id)
							->addCondition('head',$of['name'])
							->tryLoadAny();
						$existing['value'] = $form[$field_name];
						$existing->save();
					}
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

		        }catch(\Exception $e){
		            $this->api->db->rollback();
		            throw $e;
		        }	

		        if($form['want_to_add_next_employee']){
		        	$form->js(null,$form->js()->reload())->univ()->successMessage('Employee Created Successfully')->execute();
		        }
				$form->js(null,$form->js()->univ()->successMessage('Employee Created Successfully'))->univ()->redirect($this->app->url(null,['action'=>"edit",'contact_id'=>$new_employee_model->id]))->execute();
			}

		}else{
			$contact_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view');
			$contact_view->setModel($employee);
		}
		
		if($employee->loaded()){
			$portfolio_view = $this->add('xepan\hr\View_Document',['action'=> $action],'portfolio_view',['page/employee/portfolio']);
			$portfolio_view->setIdField('contact_id');
			$portfolio_view->setModel($employee,['graphical_report_id','branch_id','department','post','user','remark','salary_template','allow_login_from_anywhere'],['graphical_report_id','department_id','post_id','user_id','remark','allow_login_from_anywhere','branch_id']);
			$f=$portfolio_view->form;

			$permitted_emails_field = $f->addField('xepan/base/DropDown','permitted_emails');
			$permitted_emails_field->enableMultiSelect();
			$permitted_emails_field->setModel('xepan\communication\Model_Communication_EmailSetting');

			$emails_alowed = array_column($this->add('xepan\hr\Model_Post_Email_Association')
								->addCondition('employee_id',$employee->id)
								->getRows()
								,'emailsetting_id'
							);
			$permitted_emails_field->set($emails_alowed);

			$portfolio_view->add('Button',null,'user_edit_btn')
				->set('Edit')
				->add('VirtualPage')
					->bindEvent('Edit Selected User','click')
					->set(function($page)use($employee){
						if(!$employee['user_id']){
							$page->add('View_Error')->set('No User setted, please set any user, save and reload page');
							return;
						}

						$user_m = $employee->ref('user_id');
						$auth= $this->add('Auth');
						$auth->usePasswordEncryption('md5');
						$auth->addEncryptionHook($user_m);

						$form = $page->add('Form');
						$form->setModel($user_m,['username','password','scope']);
						$form->addSubmit('Save');

						if($form->isSubmitted()){
							$form->save();
							$form->js()->univ()->successMessage('User Saved')->closeDialog()->execute();
						}
				});


			if(!($f instanceof \Dummy) && $f->isSubmitted()){
				$f->save();

				$this->add('xepan\hr\Model_Post_Email_Association')
								->addCondition('employee_id',$employee->id)
								->deleteAll();
								
				if($f['permitted_emails']){
						foreach (explode(",",$f['permitted_emails']) as $email_setting_id) {
							$this->add('xepan\hr\Model_Post_Email_Association')
								->set('employee_id',$employee->id)
								->set('emailsetting_id',$email_setting_id)
								->save();
						}
						
					}

				$f->js(null,$f->js()->univ()->successMessage('Updated'))->reload()->execute();				
			}else{
			
			$emails_alowed_names = array_column($this->add('xepan\hr\Model_Post_Email_Association')
								->addCondition('employee_id',$employee->id)
								->getRows()
								,'emailsetting'
							);
			$portfolio_view->template->trySet('permitted_emails',implode(", ", $emails_alowed_names));	
			}

			$q = $portfolio_view->addMany('Qualification',['no_records_message'=>'No qualifications found'],'Qualification',['view/employee/qualification-grid']);
			$q->setModel($employee->ref('Qualifications'));

			$e = $portfolio_view->addMany('Experiences',['no_records_message'=>'No experience found'],'Experiences',['view/employee/experience-grid']);
			$e->setModel($employee->ref('Experiences'));

			$official_view = $this->add('xepan\hr\View_Document',['action'=> $action],'official_info',['view/employee/official-details']);
			$official_view->setIdField('contact_id');
			
			$official_view->setModel($employee,['offer_date','doj','contract_date','leaving_date','in_time','out_time','salary_payment_type'],
											   ['offer_date','doj','contract_date','leaving_date','in_time','out_time','salary_payment_type']);
			if($official_view->effective_object instanceof \Form){
				$official_view->effective_object->getElement('out_time')->setOption('showMeridian',false)
					->setOption('defaultTime',1)
					->setOption('minuteStep',1)
					->setOption('showSeconds',true);

				$official_view->effective_object->getElement('in_time')->setOption('showMeridian',false)
					->setOption('defaultTime',1)
					->setOption('minuteStep',1)
					->setOption('showSeconds',true);
			}
			// $emp_salary_view = $this->add('xepan\hr\View_Document',['action'=> $action],'official_info');
			// $emp_salary_view->setIdField('contact_id');

			$o = $official_view->addMany('EmployeeSalary',['no_records_message'=>'No document found'],'EmployeeSalary',['view/employee/emp-salary-grid']);
			$o->setModel($employee->ref('EmployeeSalary'),['salary_id','salary','amount','unit']);

			// $emp_leave_view = $this->add('xepan\hr\View_Document',['action'=> $action],'official_info');
			// $emp_leave_view->setIdField('contact_id');
			$o = $official_view->addMany('EmployeeLeaveAllow',['no_records_message'=>'No document found'],'EmployeeLeaveAllow');
			$o->setModel($employee->ref('EmployeeLeaveAllow'));
			if($o instanceof \CRUD){
				$o->grid->removeAttachment();
				$o->grid->removeColumn('action');
				$o->grid->removeColumn('employee');
			}

			$document_view = $this->add('xepan\hr\View_Document',['action'=> $action],'document_view',['page/employee/emp-document']);
			$document_view->setIdField('contact_id');
			$q = $document_view->addMany('EmployeeDocument',['no_records_message'=>'No document found'],'Document',['view/employee/emp-document-grid']);
			$q->setModel($employee->ref('EmployeeDocuments'));

			$activity_view = $this->add('xepan\base\Grid',['no_records_message'=>'No activity found'],'activity_view',['view/activity/activity-grid']);
			$activity_view->add('xepan\base\Paginator',null,'Paginator');

			$activity=$this->add('xepan\base\Model_Activity')->setOrder('created_at','desc');
			$activity->addCondition('contact_id',$_GET['contact_id']);
			$activity->tryLoadAny();
			$activity_view->setModel($activity);

			$this->addSalaryLedger();
			// $this->add('xepan\hr\')

			// $form = $this->add('Form',null,'personal_info');
			// $form->addField('Password','old_password');
			// $form->addField('Password','new_password');
			// $form->addField('Password','re_password');

			// $sf = $this->add('Form',null,'emails');
			// $field = $sf->addField('Hidden','permissions')->set(json_encode($employee->getPermissionEmail()));
			// $email=$sf->add('xepan\base\Grid',null,null,['view/employee/email-grid']);
			// $email->setModel($this->app->epan->ref('EmailSettings'));
			// $email->template->tryDel('Pannel');
			// $email->addSelectable($field);
			// $sf->addSubmit('Update');

			// if($sf->isSubmitted()){
			// 	$employee->removePermissionEmail();
			// 	$emails_permission =$this->add('xepan\hr\Model_Email_Permission'); 
			// 	$selected_emails=array();
			// 	$selected_emails = json_decode($sf['permissions'],true);
			// 	foreach ($selected_emails as $junk_id){
			// 		$emails_permission['employee_id']=$employee->id;
			// 		$emails_permission['emailsetting_id']=$junk_id;
			// 		$emails_permission->saveAndUnload();
			// 	}
			// 	$sf->js(null,$sf->js()->univ()->successMessage('Emails updated for this employee'))->reload->execute();
			// }

		}

	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}

	function addSalaryLedger(){
		// $view = $this->add('View',null,'salary_view');

		if(!$this->employee){
			$view->add('View_Error')->set('employee not added');
			return;
		}
		$this->add('xepan\hr\View_SalaryLedger',['employee_id'=>$this->employee->id],'salary_view');

	}

	function checkPhoneNo($phone_id,$phone_value,$contact_id,$form){

		 $contact = $this->add('xepan\base\Model_Contact');
        
        if($contact_id)
	        $contact->load($contact_id);

		$contactconfig_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'contact_no_duplcation_allowed'=>'DropDown'
							],
					'config_key'=>'contact_no_duplication_allowed_settings',
					'application'=>'base'
			]);
		$contactconfig_m->tryLoadAny();	

		if($contactconfig_m['contact_no_duplcation_allowed'] != 'duplication_allowed'){
	        $contactphone_m = $this->add('xepan\base\Model_Contact_Phone');
	        $contactphone_m->addCondition('id','<>',$phone_id);
	        $contactphone_m->addCondition('value',$phone_value);
			
			if($contactconfig_m['contact_no_duplcation_allowed'] == 'no_duplication_allowed_for_same_contact_type'){
				$contactphone_m->addCondition('contact_type',$contact['contact_type']);
		        $contactphone_m->tryLoadAny();
		 	}

	        $contactphone_m->tryLoadAny();
	        
	        if($contactphone_m->loaded())
	        	for ($i=1; $i <=4 ; $i++){ 
	        		if($phone_value == $form['contact_no_'.$i])
			        	$form->displayError('contact_no_'.$i,'Contact No. Already Used');
	        	}
		}	
    }

    function checkEmail($email_id,$email_value,$contact_id,$form){

    	$contact = $this->add('xepan\base\Model_Contact');
        
        if($contact_id)
	        $contact->load($contact_id);

		$emailconfig_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'email_duplication_allowed'=>'DropDown'
							],
					'config_key'=>'Email_Duplication_Allowed_Settings',
					'application'=>'base'
			]);
		$emailconfig_m->tryLoadAny();

		if($emailconfig_m['email_duplication_allowed'] != 'duplication_allowed'){
	        $email_m = $this->add('xepan\base\Model_Contact_Email');
	        $email_m->addCondition('id','<>',$email_id);
	        $email_m->addCondition('value',$email_value);
			
			if($emailconfig_m['email_duplication_allowed'] == 'no_duplication_allowed_for_same_contact_type'){
				$email_m->addCondition('contact_type',$contact['contact_type']);
			}
	        
	        $email_m->tryLoadAny();
	        
	        if($email_m->loaded())
	        	for ($i=1; $i <=4 ; $i++){ 
	        		if($email_value == $form['email_'.$i])
			        	$form->displayError('email_'.$i,'Email Already Used');
	        	}
		}	
    }


  //   function addOtherInfo($form){
  //   	// load contact other info configuration model for employee only
  //   	// loop for all fields
  //   		// check field type
  //   			// if dropdown then add dropdown
  //   			// if line then add line type field
  //   			// if datePicker then add datepicker type field
  //   			// if mandatory apply validation

 	// 	$contact_other_info_config_m = $this->add('xepan\base\Model_Config_ContactOtherInfo');
		// $contact_other_info_config_m->addCondition('for','Employee');

		// foreach($contact_other_info_config_m->config_data as $of) {
		// 	if($of['for'] != "Employee" ) continue;

		// 	if(!$of['name']) continue;

		// 	$field_name = $this->app->normalizeName($of['name']);
		// 	$field = $form->addField($of['type'],$field_name,$of['name']);
		// 	if($of['type']== 'DropDown'){
		// 		$field->setValueList(array_combine(explode(",", $of['possible_values']), explode(",", $of['possible_values'])))->setEmptyText('Please Select');
		// 	}

		// 	if($of['conditional_binding']){
		// 		$field->js(true)->univ()->bindConditionalShow(json_decode($of['conditional_binding'],true),'div.atk-form-row');
		// 	}

		// 	if($of['is_mandatory']){
		// 		$field->validate('required');
		// 	}

		// }

  //   }

}
