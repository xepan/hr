<?php


namespace xepan\hr;

class View_EasySetupWizard extends \View{
	public $vp;
	function init(){
		parent::init();

		if($_GET[$this->name.'_add_department']){
			$this->js(true)->univ()->frameURL("Department of Organization",$this->app->url('xepan_hr_department'));
			
		}
			$isDone = false;

			$action = $this->js()->reload([$this->name.'_add_department'=>1]);

				if($this->add('xepan\hr\Model_Department')->count()->getOne() > 0){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added department, visit page ? <a href="'. $this->app->url('xepan_hr_department')->getURL().'"> click here to go </a>');
				}

			$dept_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Add Other Departments')
				->setMessage('Add all the departments present in your organization. Need help! click on help icon ')
				->setHelpURL('#')	
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_add_users']){
			$this->js(true)->univ()->frameURL("Users",$this->app->url('xepan_hr_user'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_add_users'=>1]);

				if($this->add('xepan\base\Model_User')->count()->getOne() > 1){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You already added users, visit page ? <a href="'. $this->app->url('xepan_hr_user')->getURL().'"> click here to go </a>');
				}

			$user_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Create New Users')
				->setMessage('Create new users & assign user_id to particular employee')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);


		if($_GET[$this->name.'_config_user_settings']){
			$frontend_config = $this->app->epan->config;
			$reg_type=$frontend_config->getConfig('REGISTRATION_TYPE');

			$registration_config = $this->app->epan->config;
			$reg_subject = $registration_config->getConfig('REGISTRATION_SUBJECT','base');
			$reg_body = $registration_config->getConfig('REGISTRATION_BODY','base');

			$file_reg_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/registration_subject.html'));
			$file_reg_body = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/registration_body.html'));
		
			$resetpass_config = $this->app->epan->config;
			$reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT');
			$reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY');

			$file_reset_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/reset_password_subject.html'));
			$file_reset_body = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/reset_password_body.html'));
		
			$verify_config = $this->app->epan->config;
			$verify_subject = $verify_config->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$verify_body = $verify_config->getConfig('VERIFICATIONE_MAIL_BODY');
		
			$file_verification_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/verification_mail_subject.html'));
			$file_verification_body = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/verification_mail_body.html'));
			
			$update_config = $this->app->epan->config;
			$update_subject = $update_config->getConfig('UPDATE_PASSWORD_SUBJECT');
			$update_body = $update_config->getConfig('UPDATE_PASSWORD_BODY');
			
			$file_update_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/update_password_subject.html'));
			$file_update_body = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/default/update_password_body.html'));
			

			// if(!$_GET['REGISTRATION_TYPE']){
			// 	$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_usertool'));
			// }else{

				$reg_type= $frontend_config->setConfig('REGISTRATION_TYPE',"admin_activated",'base');

				$reg_subject = $registration_config->setConfig('REGISTRATION_SUBJECT',$file_reg_subject,'base');
				$reg_body = $registration_config->setConfig('REGISTRATION_BODY',$file_reg_body,'base');

				$reset_subject = $resetpass_config->setConfig('RESET_PASSWORD_SUBJECT',$file_reset_subject,'base');
				$reset_body = $resetpass_config->setConfig('RESET_PASSWORD_BODY',$file_reset_body,'base');

				$verify_subject = $verify_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$file_verification_subject,'base');
				$verify_body = $verify_config->setConfig('VERIFICATIONE_MAIL_BODY',$file_verification_body,'base');

				$update_subject = $update_config->setConfig('UPDATE_PASSWORD_SUBJECT',$file_update_subject,'base');
				$update_body = $update_config->setConfig('UPDATE_PASSWORD_BODY',$file_update_body,'base');
				
				$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_usertool'));
			// }

			$this->js(true)->reload(['REGISTRATION_TYPE',$reg_type]);
			$this->js(true)->reload(['REGISTRATION_SUBJECT',$reg_subject]);
			$this->js(true)->reload(['REGISTRATION_BODY',$reg_body]);
			$this->js(true)->reload(['RESET_PASSWORD_SUBJECT',$reset_subject]);
			$this->js(true)->reload(['RESET_PASSWORD_BODY',$reset_body]);
			$this->js(true)->reload(['VERIFICATIONE_MAIL_SUBJECT',$verify_subject]);
			$this->js(true)->reload(['VERIFICATIONE_MAIL_BODY',$verify_body]);
			$this->js(true)->reload(['UPDATE_PASSWORD_SUBJECT',$update_subject]);
			$this->js(true)->reload(['UPDATE_PASSWORD_BODY',$update_body]);
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_config_user_settings'=>1]);

			$all = $this->app->epan->config;
			$r_type = $all->getConfig('REGISTRATION_TYPE');
			$reg_sub = $all->getConfig('REGISTRATION_SUBJECT');
			$reg_body = $all->getConfig('REGISTRATION_BODY');
			$reset_pwd_sub = $all->getConfig('RESET_PASSWORD_SUBJECT');
			$reset_pwd_body = $all->getConfig('RESET_PASSWORD_BODY');
			$verify_subject = $all->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$verify_body = $all->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$update_subject = $all->getConfig('UPDATE_PASSWORD_SUBJECT');
			$update_body = $all->getConfig('UPDATE_PASSWORD_BODY');

			if(!$r_type || !$reg_sub || !$reg_body || !$reset_pwd_sub || !$reset_pwd_body || !$verify_subject || !$verify_body || !$update_body || !$update_subject){
				$isDone = false;
			}else{	
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already config the user settings, visit page ? <a href="'. $this->app->url('xepan_communication_general_emailcontent_usertool')->getURL().'"> click here to go </a>');
			}

			$user_config_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Configure Settings For New Users')
				->setMessage('Configuration setting for web user activation & deactivation mailing content')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_add_employee']){
			$this->js(true)->univ()->frameURL("Employees according Department",$this->app->url('xepan_hr_employeedetail&status=Active&action=add'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_add_employee'=>1]);

				if($this->add('xepan\hr\Model_Employee')->count()->getOne() > 1){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added employees, visit page ? <a href="'. $this->app->url('xepan_hr_employee')->getURL().'"> click here to go </a>');
				}

			$emp_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Add New Employees')
				->setMessage('Add new employees according specific departments')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);
	}
}