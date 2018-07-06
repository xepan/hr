<?php
namespace xepan\hr;

class CRUD extends \xepan\base\CRUD {
	public $status_color = [];
	public $grid_class='xepan\base\Grid';
	public $permissive_acl = false;
	public $actionsWithoutACL = false;
	public $show_spot_acl = true;
	public $add_actions = true;

	public $acl_controller=null;

	function noAttachment(){
		$this->grid->removeColumn('attachement_icon');
		$this->grid->removeColumn('attachment_icon');
	}
	
	function setModel($model,$grid_fields=null,$form_fields=null){

		$m = parent::setModel($model,$grid_fields,$form_fields);
		
		if(($m instanceof \Model) && !$this->pass_acl){
			if($this->actionsWithoutACL || isset($this->app->actionsWithoutACL)){
				$this->acl_controller = $this->add('xepan\hr\Controller_Action',['status_color'=>$this->status_color,'permissive_acl'=>$this->permissive_acl,'show_spot_acl'=>$this->show_spot_acl,'actionsWithoutACL'=>$this->actionsWithoutACL]);
			}else{
				$this->acl_controller = $this->add('xepan\hr\Controller_ACL',['status_color'=>$this->status_color,'permissive_acl'=>$this->permissive_acl,'show_spot_acl'=>$this->show_spot_acl,'actionsWithoutACL'=>$this->actionsWithoutACL,'add_actions'=>$this->add_actions]);
			}
		}
		return $m;
	}

	function recursiveRender(){
		if($this->grid->hasColumn('edit')){
			$this->grid->addOrder()->move('edit','before','delete')->now();
		}
		return parent::recursiveRender();
	}
}