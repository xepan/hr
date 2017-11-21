<?php


namespace xepan\hr;

class View_EasySetupWizard extends \View{
	public $vp;
	function init(){
		parent::init();

		/**************************************************************************
			ADD DEPARTMENT WIZARD
		**************************************************************************/	

		if($_GET[$this->name.'_add_department'])
			$this->js(true)->univ()->frameURL("Department of Organization",$this->app->url('xepan_hr_department'));
		
		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_department'=>1]);

		if($this->add('xepan\hr\Model_Department')->addCondition('name','<>','Company')->count()->getOne() > 0){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added department, visit page ? <a href="'. $this->app->url('xepan_hr_department')->getURL().'"> click here to go </a>');
		}

		$dept_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Add Company Departments')
			->setMessage('Add all the departments present in your organization.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')	
			->setAction('Click Here',$action,$isDone);

		/**************************************************************************
			ADD USER WIZARD
		**************************************************************************/	

		if($_GET[$this->name.'_add_users'])
			$this->js(true)->univ()->frameURL("Users",$this->app->url('xepan_hr_user'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_users'=>1]);

		if($this->add('xepan\base\Model_User')->count()->getOne() > 1){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You already added users, visit page ? <a href="'. $this->app->url('xepan_hr_user')->getURL().'"> click here to go </a>');
		}

		$user_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Create Users')
			->setMessage('Create login account for user, employee and customer')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**************************************************************************
			ADD EMPLOYEE WIZARD
		**************************************************************************/	

		if($_GET[$this->name.'_add_employee'])
			$this->js(true)->univ()->frameURL("Employees according Department",$this->app->url('xepan_hr_employeedetail&status=Active&action=add'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_employee'=>1]);

		if($this->add('xepan\hr\Model_Employee')->count()->getOne() > 1){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added employees, visit page ? <a href="'. $this->app->url('xepan_hr_employee')->getURL().'"> click here to go </a>');
		}

		$emp_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Add New Employees')
			->setMessage('Add employees according specific departments.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**************************************************************************
			ADD WORKING DAYS WIZARD
		**************************************************************************/	

		if($_GET[$this->name.'_add_workingweekday'])
			$this->js(true)->univ()->frameURL("Working Week Days",$this->app->url('xepan_hr_workingweekday'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_workingweekday'=>1]);

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
		
		if($week_day_model['monday'] || $week_day_model['tuesday'] || $week_day_model['wednesday'] || $week_day_model['thursday'] || $week_day_model['friday'] || $week_day_model ['saturday'] || $week_day_model['sunday']){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added working week days, visit page ? <a href="'. $this->app->url('xepan_hr_workingweekday')->getURL().'"> click here to go </a>');
		}

		$week_day_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Add Official Working Days')
			->setMessage('Add Official working days of week.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);	

		/**************************************************************************
			ADD HOLIDAYS WIZARD
		**************************************************************************/	
		
		if($_GET[$this->name.'_add_officialholiday'])
			$this->js(true)->univ()->frameURL("Official Holidays Of Your Company",$this->app->url('xepan_hr_officialholiday'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_officialholiday'=>1]);

		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');
		if($holiday_model->count()->getOne()){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added official holidays, visit page ? <a href="'. $this->app->url('xepan_hr_officialholiday')->getURL().'"> click here to go </a>');
		}

		$holiday_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Official Holidays')
			->setMessage('Add official holidays of your company.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);	
	
		/**************************************************************************
			MISC CONFIGURATION WIZARD FOR TREAT HOLIDAY BETWEEN LEAVE
		**************************************************************************/	
		if($_GET[$this->name.'_treat_holiday_between_leave']){
			$this->js(true)->univ()->frameURL("HR Misc Configuration",$this->app->url('xepan_hr_miscconfig'));
		}

		$isDone = false;

		$action = $this->js()->reload([$this->name.'_treat_holiday_between_leave'=>1]);
		$config_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'treat_holiday_between_leave'=>"Line",
										],
							'config_key'=>'HR_HOLIDAY_BETWEEN_LEAVES',
							'application'=>'hr'
						]);
		$config_model->tryLoadAny();

		if($config_model['treat_holiday_between_leave']){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You already have updated the hr misc configuration settings, visit page ? <a href="'. $this->app->url('xepan_hr_miscconfig')->getURL().'"> click here to go </a>');
		}	

		$misc_config_view = $this->add('xepan\base\View_Wizard_Step');

		$misc_config_view->setAddOn('Application - HR')
			->setTitle('Update Hr Misc Configuration')
			->setMessage('Update configuration accoding your organization norms.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**************************************************************************
			PAYSLIP LAYOUT CONFIGURATION
		**************************************************************************/	
		if($_GET[$this->name.'_payslip_layouts']){
			$personpayslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'payslip'=>'xepan\base\RichText',
							],
					'config_key'=>'PERSONPAYSLIP_LAYOUT',
					'application'=>'hr'
			]);
			$personpayslip_m->tryLoadAny();
			
			$payslip_layout_template = file_get_contents(realpath(getcwd().'/vendor/xepan/hr/templates/view/payslip-templates/duplicate-payslip-person.html'));

			if(!$personpayslip_m['payslip']){
				$personpayslip_m['payslip'] = $payslip_layout_template;
			}

			$personpayslip_m->save();

			$this->js(true)->univ()->frameURL("Payslip Layouts",$this->app->url('xepan_hr_layouts'));
		}

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_payslip_layouts'=>1]);

		$personpayslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'payslip'=>'xepan\base\RichText',
							],
					'config_key'=>'PERSONPAYSLIP_LAYOUT',
					'application'=>'hr'
			]);
		$personpayslip_m->tryLoadAny();
		
		if(!$personpayslip_m['payslip']){
			$isDone = false;
		}else{
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Templates",' You have already updated documents layouts for printing, visit page ? <a href="'. $this->app->url('xepan_commerce_layouts')->getURL().'"> click here to go </a>');
		}

		$payslip_layouts_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - HR')
			->setTitle('Set Payslip Layouts For Genrate Pdf For Printing')
			->setMessage('Please set payslip layouts for generate pdf & take print of payslip.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);


		/**************************************************************************
			Salary and Salary Templates
		**************************************************************************/	
		if($_GET[$this->name.'_add_salary'])
			$this->js(true)->univ()->frameURL("Employee Salary and Salary Template ",$this->app->url('xepan_hr_configsalary'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_salary'=>1]);

		$salary_model = $this->add('xepan\hr\Model_Salary');
		$salary_temp_model = $this->add('xepan\hr\Model_SalaryTemplate');
		
		if($salary_model->count()->getOne() && $salary_temp_model->count()->getOne()){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added Salary and Salary Template, visit page ? <a href="'. $this->app->url('xepan_hr_configsalary')->getURL().'"> click here to go </a>');
		}

		$this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Salary & Salary Template')
				->setMessage('Add/Update Salary & Salary Templates for employees.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		/**************************************************************************
			Leave and Leave Templates
		**************************************************************************/	
		if($_GET[$this->name.'_add_leave'])
			$this->js(true)->univ()->frameURL("Employee Leave and Leave Template ",$this->app->url('xepan_hr_configleave'));

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_add_leave'=>1]);

		$l_model = $this->add('xepan\hr\Model_Leave');
		$l_t_model = $this->add('xepan\hr\Model_LeaveTemplate');
		
		if($l_model->count()->getOne() && $l_t_model->count()->getOne()){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added Leave and Leave Template, visit page ? <a href="'. $this->app->url('xepan_hr_configleave')->getURL().'"> click here to go </a>');
		}

		$this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - HR')
				->setTitle('Leave & Leave Template')
				->setMessage('Add/Update Leave & Leave Templates for employees.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

	}
}