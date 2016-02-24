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
		$this->addField('namespace');
		$this->addField('document_type');
		$this->addField('action_allowed')->type('text')->defaultValue(json_encode([]));
		$this->addField('allow_add')->type('boolean')->defaultValue(true);

		$this->addHook('afterLoad',function($m){
			$m['action_allowed'] = json_decode($m['action_allowed'],true);
		});
	}
}
