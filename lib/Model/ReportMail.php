<?php

namespace xepan\hr;

class Model_ReportMail extends \xepan\communication\Model_Communication_Abstract_Email{
	public $status=['Outbox','Sent'];
	function init(){
		parent::init();
		$this->getElement('status')->defaultValue('Outbox');
		$this->addCondition('communication_type','ReportEmail');	
		$this->addCondition('direction','Out');	
	}
}
