<?php

/**
* description: xEPAN Grid, lets you defined template by options
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Grid extends \xepan\base\Grid{

	public $pass_acl=true;
	public $actionsWithoutACL=false;
	public $status_color=[];
	public $permissive_acl=false;
	public $show_spot_acl=true;

	function setModel($model,$grid_fields=null){

		$m = parent::setModel($model,$grid_fields);
		
		if(($m instanceof \xepan\base\Model_Table) && !$this->pass_acl){
			if($this->actionsWithoutACL || isset($this->app->actionsWithoutACL)){
				$this->add('xepan\hr\Controller_Action',['status_color'=>$this->status_color,'permissive_acl'=>$this->permissive_acl,'show_spot_acl'=>$this->show_spot_acl,'actionsWithoutACL'=>$this->actionsWithoutACL]);
			}else{
				$this->add('xepan\hr\Controller_ACL',['status_color'=>$this->status_color,'permissive_acl'=>$this->permissive_acl,'show_spot_acl'=>$this->show_spot_acl,'actionsWithoutACL'=>$this->actionsWithoutACL]);
			}
		}
		return $m;
	}

}
