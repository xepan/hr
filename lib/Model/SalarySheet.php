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
		
		$ss_model = $this->add('xepan\hr\Model_SalarySheet');
		$sal = $this->add('xepan\hr\Model_Salary');
		foreach ($sal->getRows() as $s) {
			$norm_name = $this->app->normalizeName($s['name']);
			$ss_model->addExpression($norm_name)->set(function($m,$q)use($s,$norm_name){
				return $q->expr('IFNULL([0],0)', [$m->refSQL('xepan\hr\EmployeeRow')->sum($norm_name)]);
			});
		}

		$ss_model->addExpression('total_amout_add')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('xepan\hr\EmployeeRow')->sum('total_amout_add')]);
		})->type('money');


		$ss_model->addExpression('total_amount_deduction')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('xepan\hr\EmployeeRow')->sum('total_amount_deduction')]);
		})->type('money');
		
		$ss_model->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('[0]-[1]',[$m->getElement('total_amout_add'),$m->getElement('total_amount_deduction')]);
		})->type('money');

		$ss_model->load($this->id);
		

		$msg = [
				'title'=>$this['name'].' Salary Sheet Approved',
				'message'=>'Salary Sheet Approved of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		$this->app->hook('salary_sheet_approved',[$ss_model]);
		
		// PAID REIMBURSEMENT
		$this->paidReimbursement();
		$this->deductionReceived();

		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." Approved by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
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
		$this->app->hook('salary_sheet_canceled',[$this]);
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

	function paidReimbursement(){

		if(!$this->loaded()) throw new \Exception("model mst loaded", 1);

		$emp_row = $this->add('xepan\hr\Model_EmployeeRow')
				->addCondition('salary_abstract_id',$this->id);
		
		$reimbursement_amount = 0;
		foreach ($emp_row as $e_row){

			$reimbursement_amount = $e_row['reimbursement_amount'];

			$reimbursemnt_dtl = $this->add('xepan\hr\Model_ReimbursementDetail');
			$reimbursemnt_dtl->addExpression('status',function($m,$q){
				return $m->refSQL('reimbursement_id')->fieldQuery('status');
			});
			$reimbursemnt_dtl->addCondition('employee_id',$e_row['employee_id']);
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


	}

	function deductionReceived(){
		if(!$this->loaded()) throw new \Exception("model mst loaded", 1);

		$emp_row = $this->add('xepan\hr\Model_EmployeeRow')
				->addCondition('salary_abstract_id',$this->id);
		
		$deduction_amount = 0;
		foreach ($emp_row as $e_row){
			$deduction_amount = $e_row['deduction_amount'];

			$deduction_mdl = $this->add('xepan\hr\Model_Deduction')
							->addCondition('employee_id',$e_row['employee_id'])
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
	}

	
}