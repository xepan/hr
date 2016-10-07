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
				->setMessage('Add all the departments present in your organization.')
				->setHelpMessage('Need help ! click on the help icon')
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
				->setMessage('Create new users & assign user_id to particular employee.')
				->setHelpMessage('Need help ! click on the help icon')
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
				->setMessage('Add new employees according specific departments.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);
	}
}