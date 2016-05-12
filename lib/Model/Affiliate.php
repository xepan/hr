<?php

namespace xepan\hr;  

class Model_Affiliate extends \xepan\base\Model_Contact{

	public $status = ['Active','InActive'];
	public $actions = [
					'Active'=>['view','edit','delete','deactivate','communication'],
					'InActive'=>['view','edit','delete','activate','communication']
					];

	function init(){
		parent::init();
		
		$this->getElement('created_by_id')->defaultValue($this->app->employee->id);
		
		$affiliate_j = $this->join('affiliate.contact_id');
		$affiliate_j->addField('narration')->type('text');
		

		$this->addCondition('type','Affiliate');
		$this->getElement('status')->defaultValue('Active');
		$this->addHook('beforeSave',$this);
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['source'];
		$this['search_string'] = $search_string;
	}
	
	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Affiliate is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/)
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}


	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Affiliate is deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}

	function rule_abcd($a){

	}

	function beforeSave($m){}
} 
