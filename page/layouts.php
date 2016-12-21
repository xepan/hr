<?php

namespace xepan\hr;

class page_layouts extends \xepan\hr\page_config{
	public $title = "Layouts";
	function init(){
		parent::init();
		
		
		/*=========== START PERSON PAYSLIP LAYOUT CONFIG =============================*/

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

		$personpayslip_form->getElement('payslip')->set($personpayslip_m['payslip'])->setFieldHint('{$presents},{$paid_leaves},{$unpaid_leaves},{$absents},{$paiddays},{$total_working_days}');
		

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

		/*=========== End PERSON PAYSLIP LAYOUT CONFIG =============================*/

		// /*=========== EMPLOYEE LIST PAYSLIP LAYOUT CONFIG =============================*/

		// $employeelistpayslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
		// 	[
		// 		'fields'=>[
		// 					'payslip'=>'xepan\base\RichText',
		// 					],
		// 			'config_key'=>'EMPLOYEE_LIST_PAYSLIP_LAYOUT',
		// 			'application'=>'hr'
		// 	]);
		// $employeelistpayslip_m->add('xepan\hr\Controller_ACL');
		// $employeelistpayslip_m->tryLoadAny();

		// $employeelistpayslip_form = $this->add('Form',null,'employees');
		// $employeelistpayslip_form->setModel($employeelistpayslip_m);

		// $employeelistpayslip_form->getElement('payslip')->set($employeelistpayslip_m['payslip'])->setFieldHint('{$presents},{$paid_leaves},{$unpaid_leaves},{$absents},{$paiddays},{$total_working_days}');
		

		// $save = $employeelistpayslip_form->addSubmit('Save')->addClass('btn btn-primary');
		// $reset = $employeelistpayslip_form->addSubmit('Reset Default')->addClass('btn btn-primary');

		// if($employeelistpayslip_form->isSubmitted()){
		// 	if($employeelistpayslip_form->isClicked($save)){
		// 		$employeelistpayslip_form->save();
		// 		$employeelistpayslip_m->app->employee
		// 	    ->addActivity("Payslip Layout Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_hr_layouts")
		// 		->notifyWhoCan(' ',' ',$employeelistpayslip_m);
		// 		return $employeelistpayslip_form->js()->univ()->successMessage('Saved')->execute();
		// 	}

		// 	if($employeelistpayslip_form->isClicked($reset)){
		// 		$ptemp = file_get_contents(realpath("../vendor/xepan/hr/templates/view/payslip-templates/duplicate-payslip-employee.html"));
				
		// 		$employeelistpayslip_m['payslip'] = $ptemp;
		// 		$employeelistpayslip_m->save();
		// 		$employeelistpayslip_m->app->employee
		// 	    ->addActivity("Payslip Layout Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_hr_layouts")
		// 		->notifyWhoCan(' ',' ',$employeelistpayslip_m);			
		// 		return $employeelistpayslip_form->js()->univ()->successMessage('Saved')->execute();
		// 	}	
		// }

		// /*=========== EMPLOYEE LIST PAYSLIP LAYOUT CONFIG =============================*/
	}

	function defaultTemplate(){
		return['page\payslip\layout'];
	}
}