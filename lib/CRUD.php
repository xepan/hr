<?php
namespace xepan\hr;

class CRUD extends \xepan\base\CRUD {

	public $grid_class='xepan\base\Grid';
	
	function setModel($model,$grid_fields=null,$form_fields=null){

		$m = parent::setModel($model,$grid_fields,$form_fields);
		
		if(($m instanceof \xepan\base\Model_Table) && !$this->pass_acl){			
			$this->add('xepan\hr\Controller_ACL');
		}
		return $m;
	}
}