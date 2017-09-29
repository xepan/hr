<?php

namespace xepan\hr;

class Model_ReimbursementDetail extends \xepan\base\Model_Table{
	public $table ="reimbursement_detail";
	public $acl = "xepan\hr\Model_Reimbursement";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Reimbursement','reimbursement_id');
		$this->addField('name');
		$this->addField('amount')->type('money');
		$this->addField('paid_amount')->type('money');
		$this->addField('date')->type('date');
		$this->addField('narration')->type('text');

		$this->addExpression('employee_id')->set(function($m,$q){
			$reimbursement_m = $this->add('xepan\hr\Model_Reimbursement');
			$reimbursement_m->addCondition('id',$m->getElement('reimbursement_id'));
			$reimbursement_m->setLimit(1);
			
			return $reimbursement_m->fieldQuery('employee_id');
		});

		$this->addExpression('due_amount',function($m,$q){
			return $q->expr('IFNULL([0],0) - IFNULL([1],0)',[$m->getElement('amount'),$m->getElement('paid_amount')]);
		});
		
		$this->addHook('afterSave',$this);
		$this->is([
				'reimbursement_id|required',
				'name|to_trim|required',
				'amount|required',
				'date|required'
			]);

	}

	function afterSave(){		
		
		$reim = $this->add('xepan\hr\Model_Reimbursement');
		$reim->load($this['reimbursement_id']);
		
		if($this['due_amount'] == 0 && $reim['amount_to_be_paid'] == 0)
				$reim->paid();
		elseif($reim['amount_paid'] > 0 && 
				$reim['amount_paid'] != $reim['amount'])
			{
				$reim['status'] = "PartiallyPaid";
				$reim->save();
			}
	}

}