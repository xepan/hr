<?php

namespace xepan\hr;

class Model_SalaryPayment extends \xepan\hr\Model_SalaryAbstract{
	
	public $status = ['Draft','Submitted','Approved','Canceled','Redraft'];
	public $actions = [
					'Draft'=>['view','edit','delete','submit'],
					'Redraft'=>['view','edit','delete','submit'],
					'Submitted'=>['view','edit','delete','approved','canceled'],
					'Approved'=>['view','edit','delete','canceled'],
					'Canceled'=>['view','edit','delete','redraft']
					];
	function init(){
		parent::init();

		$this->addCondition('type','SalaryPayment');
		
		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){
		$this->app->hook('remove_account_entry',[$this]);
	}

	function submit(){
		$this['status'] = "Submitted";
		$this->save();
		
		$msg = [
				'title'=>$this['name'].' Salary Payment Submitted',
				'message'=>'Salary Payment Submitted of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Payment ".$this['name']." submitted for approve ",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 	
	}

	function approved(){		
		$this['status'] = "Approved";
		$this->save();
		
		$msg = [
				'title'=>$this['name'].' Salary Payment Approved',
				'message'=>'Salary Payment Approved of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Payment ".$this['name']." Approved by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
		

		// $this->app->hook('create_account_entry',[$this]);
	}

	function canceled(){
		$this['status'] = "Canceled";
		$this->save();
		
		$msg = [
				'title'=>$this['name'].' Salary Payment Canceled',
				'message'=>'Salary Payment Canceled of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Payment ".$this['name']." Canceled by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);

		// $this->app->hook('remove_account_entry',[$this]);
	}

	function redraft(){
		$this['status'] = "Redraft";
		$this->save();

		$msg = [
				'title'=>"Re-Draft Salary Payment [".$this['name']."] you submitted",
				'message'=>'Re-Draft Salary Payment of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Re-Draft Salary Payment [".$this['name']."] you submitted",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);
	}

}