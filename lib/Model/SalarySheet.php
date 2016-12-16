<?php

namespace xepan\hr;

class Model_SalarySheet extends \xepan\hr\Model_SalaryAbstract{
	
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

		$this->addCondition('type','SalarySheet');

		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){
		$this->app->hook('remove_account_entry',[$this]);
	}

	function submit(){
		$this['status'] = "Submitted";
		$this->save();

		$msg = [
				'title'=>$this['name'].' Salary Sheet Submitted',
				'message'=>'Salary Sheet Submitted of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." submitted for approve ",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
	}

	function approved(){
		$this['status'] = "Approved";
		$this->save();

		$msg = [
				'title'=>$this['name'].' Salary Sheet Approved',
				'message'=>'Salary Sheet Approved of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." Approved by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
		
		$this->app->hook('create_account_entry',[$this]);
	}

	function canceled(){
		$this['status'] = "Canceled";
		$this->save();
		
		$msg = [
				'title'=>$this['name'].' Salary Sheet Canceled',
				'message'=>'Salary Sheet Canceled of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." Canceled by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);
		$this->app->hook('remove_account_entry',[$this]);
	}

	function redraft(){
		$this['status'] = "Redraft";
		$this->save();

		$msg = [
				'title'=>"Re-Draft Salary Sheet [".$this['name']."] you submitted",
				'message'=>'Re-Draft Salary Sheet of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Re-Draft Salary Sheet [".$this['name']."] you submitted",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);
	}
}