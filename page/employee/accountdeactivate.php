<?php

namespace xepan\hr;

class page_employee_accountdeactivate extends \xepan\hr\page_employee_myhr{
	public $title="Account Deactivate Request";

	function init(){
		parent::init();


		$task = $this->add('xepan\projects\Model_Task');
		$task->addCondition('status','<>',"Completed")
		    	 ->addCondition($task->dsql()->orExpr()
				     ->where('assign_to_id',$this->app->employee->id)
				     ->where($task->dsql()->andExpr()
							      ->where('created_by_id',$this->app->employee->id)
							      ->where('assign_to_id',null)
							));
		$total_uncomplete_task = $task->count()->getOne();
		if($total_uncomplete_task){
			$this->add('View')->addClass('alert alert-danger')->set("Uncomplete Task: ".$total_uncomplete_task);
		}

		if($this->app->employee['status'] != "DeactivateRequest"){
			$form = $this->add('Form');
			$form->addSubmit('Account Deactivate Request')->addClass('btn btn-danger');
			if($form->isSubmitted()){
				$this->app->employee['status'] = "DeactivateRequest";
				$this->app->employee->save();
				$form->js()->univ()->successMessage('Deactivate Request Submitted')->execute();
			}
		}else{
			$this->add('View_Info')->addClass('alert alert-info')->set('Already applied for account deactivation.');
		}

	}
}