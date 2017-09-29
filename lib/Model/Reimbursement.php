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
			'PartiallyPaid'=>['view','edit','delete','paid','manage_attachments'],
			'Paid'=>['view','edit','delete','manage_attachments']
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

		$this->addExpression('amount_to_be_paid',function($m,$q){
			return $this->add('xepan\hr\Model_ReimbursementDetail')
						->addCondition('reimbursement_id',$m->getElement('id'))
						->sum('due_amount');
		})->type('money');

		$this->addExpression('amount_paid',function($m,$q){
			return $this->add('xepan\hr\Model_ReimbursementDetail')
						->addCondition('reimbursement_id',$m->getElement('id'))
						->sum('paid_amount');
		})->type('money');

		$this->is([
				'employee_id|required',
				'name|to_trim|required'
			]);
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

		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_reimbursement_affect_salary'=>"Line",
										],
							'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$reimbursement_config_model->tryLoadAny();

		if($reimbursement_config_model['is_reimbursement_affect_salary'] === "no")
			$this->updateTransaction();
		
		$this->save();
	}

	function deleteTransactions(){
		$rimburs_model = $this->add('xepan\hr\Model_Reimbursement');
		$rimburs_model->load($this->id);

		$this->app->hook('reimbursement_canceled',[$rimburs_model]);
	}

	function updateTransaction($create_new=true){		
		$rimburs_model = $this->add('xepan\hr\Model_Reimbursement');
		$rimburs_model->load($this->id);

		$this->app->hook('reimbursement_approved',[$rimburs_model]);
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

		$reimbursement_config_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'is_reimbursement_affect_salary'=>"Line",
										],
							'config_key'=>'HR_REIMBURSEMENT_SALARY_EFFECT',
							'application'=>'hr'
						]);
		$reimbursement_config_model->tryLoadAny();

		if($reimbursement_config_model['is_reimbursement_affect_salary'] === "no")
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

	        $et->addHook('afterExecute',function($et,$transaction,$total_amount,$row_data){
				$this->paidReimbursement($row_data[0]['rows']['party']['amount'],$this['employee_id']);

				$this->app->page_action_result = $et->form->js()->univ()->closeDialog();
			});

	        $view_cash = $cash_tab->add('View');
	        $et->manageForm($view_cash,$this->id,'xepan\hr\Model_Reimbursement',$pre_filled);
	        
	        $et_bank = $this->add('xepan\accounts\Model_EntryTemplate');
	        $et_bank->loadBy('unique_trnasaction_template_code','PARTYBANKPAYMENT');

	        $et_bank->addHook('afterExecute',function($et_bank,$transaction,$total_amount,$row_data){
				$this->paidReimbursement($row_data[0]['rows']['party']['amount'],$this['employee_id']);

				$this->app->page_action_result = $et_bank->form->js()->univ()->closeDialog();
			});

	        $view_bank = $bank_tab->add('View');
	        $et_bank->manageForm($view_bank,$this->id,'xepan\hr\Model_Reimbursement',$pre_filled);
        }
    }

    function employee(){
        return $this->add('xepan\hr\Model_Employee')->tryLoad($this['employee_id']);
    }

    function paidReimbursement($amount,$emp_id){
		
		$reimbursement_amount = 0;
		$reimbursement_amount = $amount;
		
		$reimbursemnt_dtl = $this->add('xepan\hr\Model_ReimbursementDetail');
		$reimbursemnt_dtl->addExpression('status',function($m,$q){
			return $m->refSQL('reimbursement_id')->fieldQuery('status');
		});
		$reimbursemnt_dtl->addCondition('employee_id',$emp_id);
		$reimbursemnt_dtl->addCondition([['status',"Approved"],['status',"PartiallyPaid"]]);
		$reimbursemnt_dtl->addCondition('due_amount','>',0);
		$reimbursemnt_dtl->setOrder('date','asc');

		foreach ($reimbursemnt_dtl as $r_dtl) {
			if($reimbursement_amount <= 0) break;
			
			// may be removed
			$temp =  $this->add('xepan\hr\Model_ReimbursementDetail')->load($r_dtl['id']);

			if($reimbursement_amount >= $temp['due_amount']){
				$temp['paid_amount'] += $temp['due_amount'];
				$reimbursement_amount -= $temp['due_amount'];
			}else{
				$temp['paid_amount'] += $reimbursement_amount;
				$reimbursement_amount -= $reimbursement_amount;
			}
			$temp->save();
		}
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