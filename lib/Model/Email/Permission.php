<?php
namespace xepan\hr;
class Model_Email_Permission extends \xepan\base\Model_Table{
	public $table="hr_email_permission";
	public $acl=false;
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->hasOne('xepan\base\Epan_EmailSetting','emailsetting_id');
	}
}