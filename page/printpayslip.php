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

		$emp_model = $this->add('xepan\hr\Model_Employee',['addOtherInfo'=>true])->load($employee_row['employee_id']);

		$payslip_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'payslip'=>'xepan\base\RichText',
							],
					'config_key'=>'PERSONPAYSLIP_LAYOUT',
					'application'=>'hr'
			]);
		$payslip_m->tryLoadAny();

		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		$company_m->tryLoadAny();

		$emp_data = $employee_row->data;
		$emp_data['month_name'] = date("F", strtotime($emp_data['year'].'-'.$emp_data['month'].'-01'));

		$allow_leave_model = $this->add('xepan\hr\Model_Employee_LeaveAllow');
		$allow_leave_model->addCondition('employee_id',$employee_row['employee_id']);
		$allow_leave_model->addExpression('leave_taken')->set(function($m,$q){
			$emp_leave = $m->add('xepan\hr\Model_Employee_Leave')
							->addCondition('emp_leave_allow_id',$q->getField('id'))
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('from_date','>=',$q->getField('effective_date'))
							->addCondition('status','Approved')
							;
			return $q->expr('([0])',[$emp_leave->sum('no_of_leave')]);
		});

		$emp_data['employee_total_paid_leave'] = 0;
		$emp_data['employee_total_paid_leave_taken'] = 0;
		$emp_data['employee_total_paid_leave_available'] = 0;
		$emp_data['employee_total_unpaid_leave'] = 0;
		$emp_data['employee_total_unpaid_leave_taken'] = 0;
		$emp_data['employee_total_unpaid_leave_available'] = 0;
		$emp_data['employee_total_leave'] = 0;
		$emp_data['employee_total_leave_taken'] = 0;
		$emp_data['employee_total_leave_available'] = 0;

		foreach($allow_leave_model as $m){
			$ex1 = "Total_".trim($m['leave'])."_Leave";
			$ex2 = "Total_".trim($m['leave'])."_Taken";
			$ex3 = "Total_".trim($m['leave'])."_Available";
			$ex4 = trim($m['leave'])."_Effective_Date";
			$ex5 = trim($m['leave'])."_Previously_Carried_Leaves";

			$ex2_value = $m['leave_taken']?:0;
			$ex3_value = $emp_model->getAvailableLeave($this->app->now,$m['leave_id'])?:0;

			$emp_data[$ex1] = $ex2_value + $ex3_value;
			$emp_data[$ex2] = $ex2_value;
			$emp_data[$ex3] = $ex3_value;
			$emp_data[$ex4] = $m['effective_date'];
			$emp_data[$ex5] = $m['previously_carried_leaves']?:0;
			$emp_data[trim($m['leave']).'_leave_per_unit'] = $m['no_of_leave']?:0;
			$emp_data[trim($m['leave']).'_unit'] = $m['unit'];
			
			if($m['type'] == "Paid"){
				$emp_data['employee_total_paid_leave'] += $emp_data[$ex1];
				$emp_data['employee_total_paid_leave_taken'] += $emp_data[$ex2];
				$emp_data['employee_total_paid_leave_available'] += $emp_data[$ex3];
			}
			if($m['type'] == "Unpaid"){
				$emp_data['employee_total_unpaid_leave'] += $emp_data[$ex1];
				$emp_data['employee_total_unpaid_leave_taken'] += $emp_data[$ex2];
				$emp_data['employee_total_unpaid_leave_available'] += $emp_data[$ex3];
			}

		}

		$emp_data['employee_total_leave'] = $emp_data['employee_total_paid_leave'] + $emp_data['employee_total_unpaid_leave'];
		$emp_data['employee_total_leave_taken'] = $emp_data['employee_total_paid_leave_taken'] + $emp_data['employee_total_unpaid_leave_taken'];
		$emp_data['employee_total_leave_available'] = $emp_data['employee_total_paid_leave_available'] + $emp_data['employee_total_unpaid_leave_available'];

		$payslip_layout=$this->add('GiTemplate');
		$payslip_layout->loadTemplateFromString($payslip_m['payslip']);
		
		$v = $this->add('View',null,null,$payslip_layout);
		// $v->set($merge_model_array);
		// $this->app->print_r($emp_model->data,true);
		$v->template->set($emp_data);
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
		$v->template->trySet('created_at',$employee_row['created_at']);
		$v->template->trySet('net_amount_in_words',$this->add('xepan\base\Model_Document')->amountInWords($emp_data['net_amount']));
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