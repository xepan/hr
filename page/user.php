<?php
namespace xepan\hr;
class page_user extends \Page{
	public $title="User Managment";
	function init(){
		parent::init();

		$user_view=$this->add('xepan\hr\CRUD',null,null,['view/setting/user-grid']);
		$user_view->setModel('xepan\base\User');
	}
}