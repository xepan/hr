<?php

namespace xepan\hr;

class page_layouts extends \xepan\hr\page_configurationsidebar{
	public $title = "Layouts";
	function init(){
		parent::init();
		
		
		/*=========== PAYSLIP LAYOUT CONFIG =============================*/

		$personpayslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'payslip'=>'xepan\base\RichText',
							],
					'config_key'=>'PERSONPAYSLIP_LAYOUT',
					'application'=>'hr'
			]);
		$personpayslip_m->add('xepan\hr\Controller_ACL');
		$personpayslip_m->tryLoadAny();

		$personpayslip_form = $this->add('Form',null,'personpayslip');
		$personpayslip_form->setModel($personpayslip_m);

		$sal = $this->add('xepan\hr\Model_Salary');
		$all_salary = [];
		foreach ($sal->getRows() as $s) {
			$all_salary[] = '{$'.$this->app->normalizeName($s['name']).'}';
		}
		$salary_name = implode(",",$all_salary);
		// var_dump($salary_name);

		$personpayslip_form->getElement('payslip')->set($personpayslip_m['payslip'])
		->setFieldHint(implode(", ",$this->add('xepan\hr\Model_EmployeeRowDetailed')->available_fields));
		;
		// ->setFieldHint('{$company_name},{$company_address},{$company_mobile_no},{$created_at}{$employee_name},{$department},{$designation}{$date_of_joining},{$date_of_birth},{$employee_code},{$location},{$presents},{$paid_leaves},{$unpaid_leaves},{$absents},{$paiddays},{$total_working_days},'.$salary_name);
		

		$save = $personpayslip_form->addSubmit('Save')->addClass('btn btn-primary');
		$reset = $personpayslip_form->addSubmit('Reset Default')->addClass('btn btn-primary');

		if($personpayslip_form->isSubmitted()){
			if($personpayslip_form->isClicked($save)){
				$personpayslip_form->save();
				$personpayslip_m->app->employee
			    ->addActivity("Payslip Layout Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_hr_layouts")
				->notifyWhoCan(' ',' ',$personpayslip_m);
				return $personpayslip_form->js()->univ()->successMessage('Saved')->execute();
			}

			if($personpayslip_form->isClicked($reset)){
				$ptemp = file_get_contents(realpath("../vendor/xepan/hr/templates/view/payslip-templates/duplicate-payslip-person.html"));
				
				$personpayslip_m['payslip'] = $ptemp;
				$personpayslip_m->save();
				$personpayslip_m->app->employee
			    ->addActivity("Payslip Layout Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_hr_layouts")
				->notifyWhoCan(' ',' ',$personpayslip_m);			
				return $personpayslip_form->js()->univ()->successMessage('Saved')->execute();
			}	
		}
	}

	function defaultTemplate(){
		return['page\payslip\layout'];
	}
}