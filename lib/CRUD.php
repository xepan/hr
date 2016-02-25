<?php
namespace xepan\hr;

class CRUD extends \xepan\base\CRUD {
	
	function setModel($model,$grid_fields=null,$form_fields=null){

		$m = parent::setModel($model,$grid_fields,$form_fields);
		
		if((($m instanceof \xepan\base\Model_Document) || ($m instanceof \xepan\base\Model_Contact)) && !$this->pass_acl){
			$this->add('xepan\hr\Controller_ACL');
		}
		return $m;
	}
}