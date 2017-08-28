<?php
namespace xepan\hr;

class CRUD extends \xepan\base\CRUD {
	public $status_color = [];
	public $grid_class='xepan\base\Grid';
	public $permissive_acl = false;

	function noAttachment(){
		$this->grid->removeColumn('attachement_icon');
	}
	
	function setModel($model,$grid_fields=null,$form_fields=null){

		$m = parent::setModel($model,$grid_fields,$form_fields);
		
		if(($m instanceof \xepan\base\Model_Table) && !$this->pass_acl){
			$this->add('xepan\hr\Controller_ACL',['status_color'=>$this->status_color,'permissive_acl'=>$this->permissive_acl]);
		}
		return $m;
	}
}