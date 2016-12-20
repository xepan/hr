<?php

namespace xepan\hr;

class page_printpayslip extends \xepan\base\Page{
	public $title = "Employee Pay Slip";

	function init(){
		parent::init();

		$employee_row_id = $_GET['employee_row'];

		$employee_row = $this->add('xepan\hr\Model_EmployeeRow')->tryLoad($employee_row_id);
		
		if(!$employee_row->loaded()){
			$this->add('View_Error')->set('employee not found');
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
		$payslip_m->add('xepan\hr\Controller_ACL');
		$payslip_m->tryLoadAny();
		
	}
}