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
		$this->addField('date')->type('date');
		$this->addField('narration')->type('text');

	}
}