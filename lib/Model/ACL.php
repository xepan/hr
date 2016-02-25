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

		$this->addExpression('name')->set("CONCAT(namespace,'/',document_type)");
		// ->set('CONCATE(namespace,"\",document_type');

		$this->addHook('beforeSave',function($m){
			if(!$m['epan_id'] || !$m['post_id'] || !$m['document_type'] || !$m['namespace'])
				throw $this->exception('ACL Model does not have proper informations')
							->addMoreInfo('epan_id',$m['epan_id'])
							->addMoreInfo('post_id',$m['post_id'])
							->addMoreInfo('document_type',$m['document_type'])
							->addMoreInfo('namespace',$m['namespace']);
		});

		$this->addHook('afterLoad',function($m){
			$m['action_allowed'] = json_decode($m['action_allowed'],true);
		});
	}
}
