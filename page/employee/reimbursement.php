<?php

namespace xepan\hr;

class page_employee_reimbursement extends \xepan\hr\page_employee_myhr{
	public $title="My Reimbursement";
	function init(){
		parent::init();


		$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/reimbursement']);

		$model = $this->add('xepan\hr\Model_Reimbursement');
		$model->addCondition('employee_id',$this->app->employee->id);
		$model->setOrder('created_at','desc');

		$crud->setModel($model);
		$crud->addRef('Details',[
									'view_class'=>"xepan\base\CRUD",
									'label'=>"Details",
									'fields'=>['name','date','narration','amount']
								]);
	}
}