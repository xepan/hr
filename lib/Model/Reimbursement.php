<?php

namespace xepan\hr;

class Model_Reimbursement extends \xepan\hr\Model_Document{
	// public $table = "reimbursement";

	public $status = ['Draft','Submitted','Redesign','Approved','InProgress','Canceled','Completed'];
	public $actions = [
			'Draft'=>['view','edit','delete','submit','manage_attachments'],
			'Submitted'=>['view','edit','delete','inprogress','cancel','redraft','approve','manage_attachments'],
			'InProgress'=>['view','edit','delete','approve','cancel','manage_attachments'],
			'Canceled'=>['view','edit','delete','redraft','manage_attachments'],
			'Approved'=>['view','edit','delete','manage_attachments']
		];

	function init(){
		parent::init();

		$reimbursment_j = $this->join('reimbursement.document_id');
		$reimbursment_j->hasOne('xepan/hr/Employee','employee_id')->sortable(true);
		$reimbursment_j->addField('name'); // name of 
		$reimbursment_j->hasMany('xepan\hr\ReimbursementDetail','reimbursement_id',null,'Details');

		$this->getElement('created_by');
		$this->getElement('created_by_id')->system(false)->visible(true);
		$this->getElement('status')->defaultValue('Draft');
		$this->addCondition('type','Reimbursement');

		$this->addExpression('amount',$this->_dsql()->expr('IFNULL([0],0)',[$this->refSQL('Details')->sum('amount')]));
	}

	function newNumber(){
		return $this->_dsql()->del('fields')->field('max(CAST('.$this->number_field.' AS decimal))')->where('type',$this['type'])->getOne() + 1 ;
	}

	function submit(){
		$this['status'] = 'Submitted';
		$this->app->employee
		->addActivity(
					"New Reimbursement : '".$this['name']."' Submitted, Related To : ".$this['employee']."",
					$this->id/* Related Document ID*/,
					$this['employee_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyWhoCan('inprogress,cancel,redraft,approve','Submitted',$this);
		$this->save();
	}

	function approve(){
		$this['status']='Approved';
		$this->save();
		
		if($this['employee_id'] == $this['updated_by_id']){
			$id = [];
			$id = [$this['employee_id']];
			$msg = " Your Reimbursement ( ".$this['name']." ) Approved";
		}
		else{
			$id = [];
			$id = [$this['employee_id'],$this['updated_by_id']];
			$msg = "Reimbursement ( ".$this['name']." ) Approved, Related To : ".$this['employee']."";
		}
		$this->app->employee
		->addActivity(
					"Reimbursement ( ".$this['name']." ) of ".$this['employee']." Approved",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyTo([$id],$msg);
		$this->save();
	}

	function inprogress(){
		$this['status']='InProgress';
		$this->save();

		if($this['employee_id'] == $this['updated_by_id']){
			$id = [];
			$id = [$this['employee_id']];
			$msg = " Your Reimbursement ( ".$this['name']." ) is In-Progress";
		}
		else{
			$id = [];
			$id = [$this['employee_id'],$this['updated_by_id']];
			$msg = "Reimbursement ( ".$this['name']." ) is In-Progress, Related To : ".$this['employee']."";
		}

		$this->app->employee
		->addActivity(
					"Reimbursement ( ".$this['name']." ) is Inprogress",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyTo([$id],$msg);
		$this->save();
	}

	function cancel(){
		$this['status']='Canceled';
		$this->save();

		if($this['employee_id'] == $this['updated_by_id']){
			$id = [];
			$id = [$this['employee_id']];
			$msg = " Your Reimbursement ( ".$this['name']." ) has Canceled";
		}
		else{
			$id = [];
			$id = [$this['employee_id'],$this['updated_by_id']];
			$msg = "Reimbursement ( ".$this['name']." ) has Canceled, Related To : ".$this['employee']."";
		}

		$this->app->employee
		->addActivity(
					"Reimbursement ( ".$this['name']." ) has Canceled",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyTo([$id],$msg);
		$this->save();
	}	

	function redraft(){
		$this['status']='Draft';
		$this->save();

		if($this['employee_id'] == $this['updated_by_id']){
			$id = [];
			$id = [$this['employee_id']];
			$msg = " Your Reimbursement ( ".$this['name']." ) Re-Drafted";
		}
		else{
			$id = [];
			$id = [$this['employee_id'],$this['updated_by_id']];
			$msg = "Reimbursement ( ".$this['name']." ) Re-Drafted, Related To : ".$this['employee']."";
		}

		$this->app->employee
		->addActivity(
					"Reimbursement ( ".$this['name']." ) Re-Draft",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyTo([$id],$msg);
		$this->save();
	}
}