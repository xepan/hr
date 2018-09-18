<?php

namespace xepan\hr;

/**
* 
*/
class Model_Employee_Leave extends \xepan\base\Model_Table{
	public $table ="employee_leave";
	public $actions= [
						'Draft'=>['view','edit','delete','submit'],
						'Submitted'=>['view','edit','delete','approve','reject'],
						'Approved'=>['view','edit','delete','reject'],
						'Rejected'=>['view','edit','delete','approve']
					];

	public $acl_type ="Employee_Leave";
	public $month;
	public $year;
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\hr\Employee_LeaveAllow','emp_leave_allow_id')->caption('Leave Type');
		$this->addField('from_date')->type('date');
		$this->addField('to_date')->type('date');
		$this->addField('status')->enum(['Draft','Submitted','Approved','Rejected'])->defaultValue('Draft');
		$this->addField('narration')->type('text');

		$this->addExpression('no_of_leave')->set(function($m,$q){
			return $q->expr('(DATEDIFF([0],[1]))  + 1',[$q->getField('to_date'),$q->getField('from_date')]);
		})->caption('No of Days');

		$this->addExpression('month')->set('MONTH(from_date)');
		$this->addExpression('year')->set('YEAR(from_date)');
		$this->addExpression('month_leaves')->set('DATEDIFF(to_date,from_date) + 1');

		$this->addExpression('leave_type')->set($this->refSQL('emp_leave_allow_id')->fieldQuery('type'));
		$this->addExpression('employee')->set($this->refSQL('employee_id')->fieldQuery('name'));
		
		if(!$this->month) $this->month = date('m',strtotime($this->app->monthFirstDate()));
		if(!$this->year) $this->year = date('Y', strtotime($this->app->monthFirstDate()));

		$this->addExpression('month_from_date')->set(function($m,$q){

			$month_start_date = date($this->year.'-'.$this->month.'-01');

			return $q->expr("IF(([from_date] > '[month_start_date]'),[from_date],'[month_start_date]')",
														[
															'from_date'=>$m->getElement('from_date'),
															'month_start_date'=>$month_start_date
														]);
		})->type('date');

		$this->addExpression('month_to_date')->set(function($m,$q){

			$month_to_date =  date('Y-m-t',strtotime($this->year.'-'.$this->month.'-01'));

			return $q->expr("IF(([to_date] < '[month_to_date]'),[to_date],'[month_to_date]')",
														[
															'to_date'=>$m->getElement('to_date'),
															'month_to_date'=>$month_to_date
														]);
		})->type('date');

		$this->addHook('beforeSave',$this);
	}

	function submit(){
		$this['status']='Submitted';
		$this->app->employee
            ->addActivity("Employee '".$this['employee']."' Submitted/ Applied ".$this['no_of_leave']." ".$this['leave_type']." Leave", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('approve,reject','Submitted',$this);
		$this->save();
	}
	function approve(){
		$this['status']='Approved';
		$this->app->employee
            ->addActivity("Employee '".$this['employee']." ".$this['no_of_leave']." ".$this['leave_type']."' Leave Approved", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employeedetail&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('view,reject','Approved',$this);
		$this->save();
	}
	function reject(){
		$this['status']='Rejected';
		$this->app->employee
            ->addActivity("Employee '".$this['employee']." ".$this['no_of_leave']." ".$this['leave_type']."' Leave Rejected", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employee_hr&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('view,approve','Rejected',$this);
		$this->save();
	}

	function beforeSave(){
		if($this['to_date'] < $this['from_date'])
			throw $this->exception('"From Date " should be less than from "To Date" of Leave','ValidityCheck')->setField('to_date');
	}
}