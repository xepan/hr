<?php

namespace xepan\hr;

class Model_Deduction extends \xepan\hr\Model_Document{
	// public $table = "deduction";
	
	public $status = ['Draft','Submitted','Approved','Canceled','Recieved'];
	public $actions = [
			'Draft'=>['view','edit','delete','submit','manage_attachments'],
			'Submitted'=>['view','edit','delete','cancel','redraft','approve','manage_attachments'],
			'Canceled'=>['view','edit','delete','redraft','manage_attachments'],
			'Approved'=>['view','edit','delete','received','cancel','manage_attachments'],
			'PartiallyRecieved'=>['view','edit','delete','received','manage_attachments'],
			'Recieved'=>['view','edit','delete','manage_attachments']
		];

	function init(){
		parent::init();

		$deduction_j = $this->join('deduction.document_id');
		$deduction_j->hasOne('xepan/hr/Employee','employee_id')->sortable(true);
		$deduction_j->addField('name')->caption('Reason');
		$deduction_j->addField('amount')->type('money');
		$deduction_j->addField('narration')->type('text');
		$deduction_j->addField('received_amount')->type('money');
		
		$this->addExpression('due_amount',function($m,$q){
			return $q->expr('IFNULL([0],0) - IFNULL([1],0)',[$m->getElement('amount'),$m->getElement('received_amount')]);
		});

		
		$this->getElement('created_at')->system(false)->visible(true)->editable(true);
		$this->getElement('created_by');
		$this->getElement('created_by_id')->system(true)->visible(true);
		$this->getElement('status')->defaultValue('Draft');
		$this->addCondition('type','Deduction');
	}

	function submit(){
		$this['status'] = 'Submitted';
		$this->app->employee
		->addActivity(
					"New Deduction : '".$this['name']."' Submitted, Related To : ".$this['employee']."",
					$this->id/* Related Document ID*/,
					$this['employee_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_deduction&deduction_id=".$this->id.""
				)
		->notifyWhoCan('cancel,redraft,approve','Submitted',$this);
		$this->save();
	}

	function approve(){
		$this['status']='Approved';
		$this->save();
		
		$id = [];
		$id = [$this['employee_id'],$this['updated_by_id']];
		$msg = "Deduction ( ".$this['name']." ) Approved, Related To : ".$this['employee']."";
		$this->app->employee
		->addActivity(
					"Deduction ( ".$this['name']." ) of ".$this['employee']." Approved",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_deduction&deduction_id=".$this->id.""
				)
		->notifyTo($id,$msg);

		$deduction_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_deduction_affect_salary'=>"Line",
										],
							'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$deduction_config_model->tryLoadAny();

		if($deduction_config_model['is_deduction_affect_salary'] === "no")
			$this->updateTransaction();

		$this->save();
	}

	function deleteTransactions(){
		$deduction_model = $this->add('xepan\hr\Model_Deduction');
		$deduction_model->load($this->id);

		$this->app->hook('deduction_canceled',[$deduction_model]);
	}

	function updateTransaction($create_new=true){		
		$deduction_model = $this->add('xepan\hr\Model_Deduction');
		$deduction_model->load($this->id);

		$this->app->hook('deduction_approved',[$deduction_model]);
	}

	function cancel(){
		$this['status']='Canceled';
		$this->save();

		$id = [];
		$id = [$this['employee_id'],$this['updated_by_id']];
		$msg = "Deduction ( ".$this['name']." ) has Canceled, Related To : ".$this['employee']."";

		$this->app->employee
		->addActivity(
					"Deduction ( ".$this['name']." ) has Canceled",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_deduction&deduction_id=".$this->id.""
				)
		->notifyTo($id,$msg);

		$deduction_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_deduction_affect_salary'=>"Line",
										],
							'config_key'=>'HR_DEDUCTION_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$deduction_config_model->tryLoadAny();

		if($deduction_config_model['is_deduction_affect_salary'] === "no")
			$this->deleteTransactions();
	
		$this->save();
	}	

	function redraft(){
		$this['status']='Draft';
		$this->save();

		$id = [];
		$id = [$this['employee_id'],$this['updated_by_id']];
		$msg = "Deduction ( ".$this['name']." ) Re-Drafted, Related To : ".$this['employee']."";
		$this->app->employee
		->addActivity(
					"Deduction ( ".$this['name']." ) Re-Drafted",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_deduction&deduction_id=".$this->id.""
				)
		->notifyTo($id,$msg);
		$this->save();
	}

	function page_received($page){
		$application_mdl = $this->add('xepan\base\Model_Epan_InstalledApplication');
        $application_mdl->addCondition('application_namespace','xepan\accounts');
        $application_mdl->tryLoadAny();

        if(!$application_mdl->loaded()){
			$page->add('View')->set("This services is not available in your available package")->addClass('project-box-header green-bg well-sm')->setstyle('color','green');
        } 
        else
        {
        	$tabs = $page->add('Tabs');
	        $cash_tab = $tabs->addTab('Cash Recieved');
	        $bank_tab = $tabs->addTab('Bank Recieved');
	        
	        $ledger = $this->employee()->ledger();
	        $pre_filled =[
	            1 => [
	                'party' => ['ledger'=>$ledger,'amount'=>$this['amount'],'currency'=>$this->app->epan->default_currency->id]
	            ]
	        ];

	        $et = $this->add('xepan\accounts\Model_EntryTemplate');
	        $et->loadBy('unique_trnasaction_template_code','ANYPARTYCASHRECEIVED');

	        $et->addHook('afterExecute',function($et,$transaction,$total_amount,$row_data){
				$this->deductionReceived($row_data[0]['rows']['party']['amount'],$this['employee_id']);

				$this->app->page_action_result = $et->form->js()->univ()->closeDialog();
			});

	        $view_cash = $cash_tab->add('View');
	        $et->manageForm($view_cash,$this->id,'xepan\hr\Model_Deduction',$pre_filled);

	        $et_bank = $this->add('xepan\accounts\Model_EntryTemplate');
	        $et_bank->loadBy('unique_trnasaction_template_code','ANYPARTYBANKRECEIVED');

	        $et_bank->addHook('afterExecute',function($et_bank,$transaction,$total_amount,$row_data){
				$this->deductionReceived($row_data[0]['rows']['party']['amount'],$this['employee_id']);

				$this->app->page_action_result = $et_bank->form->js()->univ()->closeDialog();
			});

	        $view_bank = $bank_tab->add('View');
	        $et_bank->manageForm($view_bank,$this->id,'xepan\hr\Model_Deduction',$pre_filled);
        	// $this->app->page_action_result = $et_bank->form->js()->univ()->closeDialog();
        }
    }

    function employee(){
        return $this->add('xepan\hr\Model_Employee')->tryLoad($this['employee_id']);
    }

    function deductionReceived($amount,$emp_id){
		
		$deduction_amount = 0;
		$deduction_amount = $amount;
		
		$deduction_mdl = $this->add('xepan\hr\Model_Deduction')
							->addCondition('employee_id',$emp_id)
							->addCondition([['status','Approved'],['status','PartiallyRecieved']]);
			
		foreach ($deduction_mdl as $mdl) {
			if($deduction_amount <= 0) continue;

			if($deduction_amount >= $mdl['due_amount']){
				$mdl['received_amount'] += $mdl['due_amount'];
				$deduction_amount -= $mdl['due_amount'];
				$mdl['status'] = "Recieved";
			}else{
				$mdl['received_amount'] += $deduction_amount;
				$deduction_amount -= $deduction_amount;
				$mdl['status'] = "PartiallyRecieved";
			}
				$mdl->saveAndUnload();
		}
	}

	function received(){

		$this['status']='Recieved';
		$this->save();
		$id = [];
		$id = [$this['employee_id'],$this['updated_by_id']];
		
		$msg = " Deduction ( ".$this['name']." ) successfully recieved from Employee : ".$this['employee']." ";
		$this->app->employee
		->addActivity(
					"Deduction ( ".$this['name']." ) from ".$this['employee']." Recieved",
					$this->id/* Related Document ID*/,
					$this['contact_id'] /*Related Contact ID*/,
					null,
					null,
					"xepan_hr_deduction&deduction_id=".$this->id.""
				)
		->notifyTo($id,$msg);
		$this->save();
	}
}