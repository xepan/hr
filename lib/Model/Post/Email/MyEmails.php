<?php
namespace xepan\hr;

class Model_Post_Email_MyEmails extends \xepan\communication\Model_Communication_EmailSetting{
	public $acl=false;
	
	function init(){
		parent::init();
		$this->addCondition('is_active',true);
		$ass_j=$this->join('post_email_association.emailsetting_id');
		$ass_j->addField('post_id');
		$ass_j->addField('employee_id');
		if(isset($this->app->employee))
			$this->addCondition([['post_id',$this->app->employee['post_id']],['employee_id',$this->app->employee->id]]);

		$this->_dsql()->group('emailsetting.id');
		
		$this->addExpression('post_email')->set(function($m,$q){
			return $q->getField('email_username');
		});
	}
}