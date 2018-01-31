<?php

namespace xepan\hr;

class Model_EmployeeRowDetailed extends Model_EmployeeRow {

	public $available_fields=[];

	function init(){
		parent::init();

		$sheet_fields = ['month','year'];
		$row_fields = ['employee','total_amount','presents','paid_leaves','unpaid_leaves','absents','paiddays','total_working_days','reimbursement_amount','deduction_amount'];

		$emp_fields = array_keys($this->add('xepan\hr\Model_Employee',['addOtherInfo'=>true])->tryLoadAny()->data);

		$to_remove = ['id','name'];
		$emp_fields = array_diff($emp_fields, $to_remove);

		$salaries = [];
		foreach ($this->add('xepan\hr\Model_Salary') as $s) {
			$salaries[] =  $s['name'];
		};

		foreach ($sheet_fields as $s_fields) {
			$this->addExpression($s_fields)->set($this->refSQL('salary_abstract_id')->fieldQuery($s_fields));
		}
		foreach ($emp_fields as $emp_field) {
			$this->addExpression($emp_field)->set($this->add('xepan\hr\Model_Employee',['addOtherInfo'=>true])->addCondition('id',$this->getField('employee_id'))->fieldQuery($emp_field));
		}
		foreach ($salaries as $s) {
			$this->addExpression($s)->set($this->refSQL('SalaryDetail')->addCondition('salary',$s)->fieldQuery('amount'));
		}

		$this->available_fields = array_merge($sheet_fields,$row_fields,$emp_fields,$salaries);

	}
}