<?php

namespace xepan\hr;

class Model_Reimbursement extends \xepan\hr\Model_Document{
	// public $table = "reimbursement";

	public $status = ['Draft','Submitted','Approved','Canceled','Paid'];
	public $actions = [
			'Draft'=>['view','edit','delete','submit','manage_attachments'],
			'Submitted'=>['view','edit','delete','cancel','redraft','approve','manage_attachments'],
			'Canceled'=>['view','edit','delete','redraft','manage_attachments'],
			'Approved'=>['view','edit','delete','paid','cancel','manage_attachments'],
			'Paid'=>['view','edit','delete','cancel','manage_attachments']
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
		->notifyWhoCan('cancel,redraft,approve','Submitted',$this);
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
		->notifyTo($id,$msg);
		$this->updateTransaction();
		$this->save();
	}

	function deleteTransactions(){
		$rimburs_model = $this->add('xepan\hr\Model_Reimbursement');
		$rimburs_model->load($this->id);

		$this->app->hook('reimbursement_canceled',[$rimburs_model]);
	}

	function updateTransaction($delete_old=true,$create_new=true){		
		$rimburs_model = $this->add('xepan\hr\Model_Reimbursement');
		$rimburs_model->load($this->id);

		if($delete_old){			
		// reimbursement model transaction have always one entry in transaction
			$old_amount = $this->app->hook('reimbursement_canceled',[$rimburs_model]);
		}

		if($create_new){
			$this->app->hook('reimbursement_approved',[$rimburs_model]);
		}
		
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
		->notifyTo($id,$msg);
		$this->deleteTransactions();
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
		->notifyTo($id,$msg);
		$this->save();
	}

	function page_paid($page){
		$application_mdl = $this->add('xepan\base\Model_Epan_InstalledApplication');
        $application_mdl->addCondition('application_namespace','xepan\accounts');
        $application_mdl->tryLoadAny();

        if(!$application_mdl->loaded()){
			$page->add('View')->set("This services is not available in your available packagein your ")->addClass('project-box-header green-bg well-sm')->setstyle('color','green');
        } 
        else
        {
        	$tabs = $page->add('Tabs');
	        $cash_tab = $tabs->addTab('Cash Payment');
	        $bank_tab = $tabs->addTab('Bank Payment');
	        
	        $ledger = $this->employee()->ledger();
	        $pre_filled =[
	            1 => [
	                'party' => ['ledger'=>$ledger,'amount'=>$this['amount'],'currency'=>$this->app->epan->default_currency->id]
	            ]
	        ];

	        $et = $this->add('xepan\accounts\Model_EntryTemplate');
	        $et->loadBy('unique_trnasaction_template_code','PARTYCASHPAYMENT');

	        $view_cash = $cash_tab->add('View');
	        $et->manageForm($view_cash,$this->id,'xepan\hr\Model_Reimbursement',$pre_filled);

	        $et_bank = $this->add('xepan\accounts\Model_EntryTemplate');
	        $et_bank->loadBy('unique_trnasaction_template_code','PARTYBANKPAYMENT');

	        $view_bank = $bank_tab->add('View');
	        $et_bank->manageForm($view_bank,$this->id,'xepan\hr\Model_Reimbursement',$pre_filled);
        	// $this->app->page_action_result = $et_bank->form->js()->univ()->closeDialog();
        }
        	$this->paid();
    }

    function employee(){
        return $this->add('xepan\hr\Model_Employee')->tryLoad($this['employee_id']);
    }

	function paid(){

		$this['status']='Paid';
		$this->save();
		$id = [];
		$id = [$this['employee_id'],$this['updated_by_id']];
		
		$msg = " Reimbursement ( ".$this['name']." ) successfully paid to Employee : ".$this['employee']." ";
		$this->app->employee
		->addActivity(
					"Reimbursement ( ".$this['name']." ) of ".$this['employee']." Paid",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_reimbursement&reimbursement_id=".$this->id.""
				)
		->notifyTo($id,$msg);
		$this->save();
	}
}