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
						'Approved'=>['view','edit','delete'],
						'Rejected'=>['view','edit','delete'],
					];

	public $acl_type ="Employee_Leave";
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);
		$this->hasOne('xepan\hr\Employee_LeaveAllow','emp_leave_allow_id');
		$this->addField('from_date')->type('date');
		$this->addField('to_date')->type('date');
		$this->addField('status')->enum(['Draft','Submitted','Approved','Rejected'])->defaultValue('Draft');

		$this->addExpression('no_of_leave')->set(function($m,$q){
			return $q->expr('(DATEDIFF([0],[1]))',[$q->getField('to_date'),$q->getField('from_date')]);
		});

		$this->addExpression('employee')->set($this->refSQL('created_by_id')->fieldQuery('name'));
	}

	function submit(){
		$this['status']='Submitted';
		$this->app->employee
            ->addActivity("Employee '".$this->app->employee['name']."' has been Submitted Leave", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employee_hr&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}
	function approve(){
		$this['status']='Approved';
		$this->app->employee
            ->addActivity("Employee '".$this->app->employee['name']."' has been Approved Leave", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employee_hr&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}
	function reject(){
		$this['status']='Rejected';
		$this->app->employee
            ->addActivity("Employee '".$this->app->employee['name']."' has been Rejected Leave", null/* Related Document ID*/, $this['employee_id'] /*Related Contact ID*/,null,null,"xepan_hr_employee_hr&contact_id=".$this['employee_id']."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}
}