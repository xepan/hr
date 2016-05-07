<?php
namespace xepan\hr;
class page_user extends \Page{
	public $title="User Managment";
	function init(){
		parent::init();

		$user_m= $this->add('xepan\base\Model_User');
		$this->app->auth->addEncryptionHook($user_m);
		$user_m->addHook('beforeSave',function($m){
			$m['password']=$m->app->auth->encryptPassword($m['password'],$m['username']);
		});

		$user_view=$this->add('xepan\hr\CRUD',null,null,['view/setting/user-grid']);
		$user_view->setModel($user_m);
	}
}