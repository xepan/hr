<?php
namespace xepan\hr;

class Model_Post_Email_Association extends \xepan\base\Model_Table{
	public $table="post_email_association";
	public $acl=false;
	
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Post','post_id');
		$this->hasOne('xepan\base\Epan_EmailSetting','emailsetting_id');
	}
}