<?php

/**
* description: ACL Model to save ACL
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Model_ACL extends \xepan\base\Model_Table {
	public $table='acl';

	function init(){
		parent::init();
		
		$this->hasOne('xepan\base\Epan','epan_id');
		$this->hasOne('xepan\hr\Post','post_id');
		$this->addField('document_type');
		$this->addField('status');
		$this->addField('action_allowed')->type('text')->defaultValue(json_encode([]));

		$this->addHook('afterLook',function($m){
			$m['action_allowed'] = json_decode($m['action_allowed'],true);
		});
	}
}
