<?php
namespace xepan\hr;
class page_user extends \xepan\base\Page{
	public $title="User Managment";
	function init(){
		parent::init();

		$user_m= $this->add('xepan\base\Model_User');
		$user_m->add('xepan\hr\Controller_SideBarStatusFilter');
		if($status = $this->api->stickyGET('status'))
			$user_m->addCondition('status',$status);
		
		$auth= $this->add('Auth');
		$auth->usePasswordEncryption('md5');
		$auth->addEncryptionHook($user_m);
		// $user_m->addHook('beforeSave',function($m){
		// 	$m['password']=$m->app->auth->encryptPassword($m['password'],$m['username']);
		// });

		$user_view=$this->add('xepan\hr\CRUD',null,null,['view/setting/user-grid']);
		$user_view->grid->addPaginator(50);
		$user_view->grid->addQuickSearch(['username']);
		$user_view->add('xepan\base\Controller_Avatar',['options'=>['size'=>50,'border'=>['width'=>0]],'name_field'=>'username','default_value'=>'']);		
		$user_view->setModel($user_m,array('created_by_id','username','password','type','scope','status'),array('username','type','status','action','related_contact','related_contact_type','scope'));

	}
}