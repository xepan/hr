<?php


namespace xepan\hr;

class View_EasySetupWizard extends \View{
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
				->setTitle('Department of Company/Organization')
				->setMessage('Add the avilable department in Organization')
				->setHelpURL('#')	
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_add_users']){
			$this->js(true)->univ()->frameURL("Users",$this->app->url('xepan_hr_user'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_add_users'=>1]);

				if($this->add('xepan\base\Model_User')->count()->getOne() > 0){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You already added users, visit page ? <a href="'. $this->app->url('xepan_hr_user')->getURL().'"> click here to go </a>');
				}

			$user_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Creation of Users')
				->setMessage('Create users & assign user_id to particular employee')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_config_user_settings']){

			$frontend_config->setConfig('REGISTRATION_TYPE','Self Activation Via Email','base');
			$registration_config->setConfig('REGISTRATION_SUBJECT',$form['subject'],'base');
			$registration_config->setConfig('REGISTRATION_BODY',$form['Body'],'base');
			$resetpass_config->setConfig('RESET_PASSWORD_SUBJECT','Registration Mail For Active Account','base');
			$resetpass_config->setConfig('RESET_PASSWORD_BODY','Hello User, this is an acitvation mail','base');
			$verify_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$form['subject'],'base');
			$verify_config->setConfig('VERIFICATIONE_MAIL_BODY',$form['body'],'base');
			$update_config->setConfig('UPDATE_PASSWORD_SUBJECT',$form['subject'],'base');
			$update_config->setConfig('UPDATE_PASSWORD_BODY',$form['body'],'base');


			$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_usertool'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_config_user_settings'=>1]);

				if($this->add('xepan\base\Model_User')->count()->getOne() > 0){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You already config the user settings, visit page ? <a href="'. $this->app->url('xepan_communication_general_emailcontent_usertool')->getURL().'"> click here to go </a>');
				}

			$user_config_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Config the User Settings')
				->setMessage('Set the config for web user activation & deactivation')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_add_employee']){
			$this->js(true)->univ()->frameURL("Employees according Department",$this->app->url('xepan_hr_employeedetail&status=Active&action=add'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_add_employee'=>1]);

				if($this->add('xepan\hr\Model_Employee')->count()->getOne() > 0){
					$isDone = true;
					$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added employees, visit page ? <a href="'. $this->app->url('xepan_hr_employee')->getURL().'"> click here to go </a>');
				}

			$emp_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Employees of their related Department')
				->setMessage('Add the employees, according to their specific departments')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);


	}
}