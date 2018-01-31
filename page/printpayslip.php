<?php

namespace xepan\hr;

class page_printpayslip extends \xepan\base\Page{
	public $title = "Employee Pay Slip";

	function init(){
		parent::init();

		$employee_row_id = $_GET['employee_row'];

		$employee_row = $this->add('xepan\hr\Model_EmployeeRowDetailed')->tryLoad($employee_row_id);
		
		if(!$employee_row->loaded()){
			$this->add('View_Error')->set('No record found');
			return;
		}

		$payslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'payslip'=>'xepan\base\RichText',
							],
					'config_key'=>'PERSONPAYSLIP_LAYOUT',
					'application'=>'hr'
			]);
		$payslip_m->tryLoadAny();

		$company_m = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'company_name'=>"Line",
								'company_owner'=>"Line",
								'mobile_no'=>"Line",
								'company_email'=>"Line",
								'company_address'=>"Line",
								'company_pin_code'=>"Line",
								'company_description'=>"xepan\base\RichText",
								'company_logo_absolute_url'=>"Line",
								'company_twitter_url'=>"Line",
								'company_facebook_url'=>"Line",
								'company_google_url'=>"Line",
								'company_linkedin_url'=>"Line",
								],
					'config_key'=>'COMPANY_AND_OWNER_INFORMATION',
					'application'=>'communication'
				]);
		
		$company_m->tryLoadAny();

		$payslip_layout=$this->add('GiTemplate');
		$payslip_layout->loadTemplateFromString($payslip_m['payslip']);

		$v = $this->add('View',null,null,$payslip_layout);
		// $v->set($merge_model_array);
		$v->setModel($employee_row);
		// $v->template->trySet('employee_name',$emp['name']);
		// $v->template->trySet('department',$emp['department']);
		// $v->template->trySet('designation',$emp['posts']);
		// $v->template->trySet('date_of_joining',$emp['doj']);
		// $v->template->trySet('employee_code',$emp['code']);
		// $v->template->trySet('location',$emp['state']);
		// $v->template->trySet('date_of_birth',$emp->ref('Events')->addCondition('head','DOB')->get('value'));
		$v->template->trySet('company_name',$company_m['company_name']);
		$v->template->trySet('company_address',$company_m['company_address']);
		$v->template->trySet('company_mobile_no',$company_m['mobile_no']);
		$v->template->trySet('created_at',$v->model['created_at']);
		// $v->template->trySet('total_amount_deduction',$v->model['total_amount_deduction']);
		// $v->template->trySet('net_amount',$v->model['net_amount']);

		// $sal = $this->add('xepan\hr\Model_Salary');

		// foreach ($sal->getRows() as $s) {
		// 	echo $this->app->normalizeName($s['name']) . "  " .$employee_row[$this->app->normalizeName($s['name'])]. "<br/>";
		// 	// $v->template->trySetHTML($this->app->normalizeName($s['name']),$employee_row[$this->app->normalizeName($s['name'])]);
		// 	// $v->template->trySetHTML($this->app->normalizeName($s['name']),$s['name']);
		// }

		// echo "<pre>";
		// print_r($v->getHTML());
	}
}