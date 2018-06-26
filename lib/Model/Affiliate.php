<?php

namespace xepan\hr;  

class Model_Affiliate extends \xepan\base\Model_Contact{

	public $status = ['Active','InActive'];
	public $actions = [
					'Active'=>['view','edit','delete','deactivate','communication'],
					'InActive'=>['view','edit','delete','activate','communication']
					];
	public $contact_type = "Affiliate";
	
	function init(){
		parent::init();
		
		$this->getElement('created_by_id')->defaultValue($this->app->employee->id);
		
		$affiliate_j = $this->join('affiliate.contact_id');
		$affiliate_j->addField('narration')->type('text');
		

		$this->addCondition('type','Affiliate');
		$this->getElement('status')->defaultValue('Active');
		$this->addHook('beforeSave',[$this,'updateSearchString']);
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['name'];
		$search_string .=" ". str_replace("<br/>", " ", $this['contacts_str']);
		$search_string .=" ". str_replace("<br/>", " ", $this['emails_str']);
		$search_string .=" ". $this['source'];
		$search_string .=" ". $this['type'];
		$search_string .=" ". $this['city'];
		$search_string .=" ". $this['state'];
		$search_string .=" ". $this['pin_code'];
		$search_string .=" ". $this['organization'];
		$search_string .=" ". $this['post'];
		$search_string .=" ". $this['website'];
		$search_string .=" ". $this['narration'];

		$this['search_string'] = $search_string;
	}
	
	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Affiliate : '".$this['name']."' is now active", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_affiliatedetails&contact_id=".$this->id."")
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}


	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Affiliate : '".$this['name']."' has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_hr_affiliatedetails&contact_id=".$this->id."")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}

	function rule_abcd($a){

	}

} 
