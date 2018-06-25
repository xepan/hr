<?php
namespace xepan\hr;

class Model_Post_Email_Association extends \xepan\base\Model_Table{
	public $table="post_email_association";
	public $acl=false;
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Post','post_id');
		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\communication\Communication_EmailSetting','emailsetting_id');

		$this->addExpression('name')->set(function($m,$q){
			return $m->refSQL('emailsetting_id')->fieldQuery('name');
		});
	}
}