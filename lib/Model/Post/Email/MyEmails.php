<?php
namespace xepan\hr;

class Model_Post_Email_MyEmails extends \xepan\communication\Model_Communication_EmailSetting{
	public $acl=false;
	
	function init(){
		parent::init();

		$ass_j=$this->join('post_email_association.emailsetting_id');
		$ass_j->addField('post_id');
		$this->addCondition('post_id',$this->app->employee['post_id']);

	}
}