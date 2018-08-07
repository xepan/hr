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

	public $acl=false;

	public $for_model=null;

	function init(){
		parent::init();
		
		// $this->hasOne('xepan\base\Epan','epan_id');
		$this->hasOne('xepan\hr\Post','post_id');
		$this->addField('namespace');
		$this->addField('type');
		$this->addField('action_allowed')->type('text')->defaultValue(json_encode([]));
		$this->addField('allow_add')->type('boolean')->defaultValue(true);
		$this->addField('is_branch_restricted')->type('boolean')->defaultValue(true);

		$this->addExpression('name')->set("CONCAT(type, ' [',namespace,']')");

		$this->addHook('beforeSave',function($m){
			if(!$m['post_id'] || !$m['type'] || !$m['namespace']){
				if(!$m['post_id']){
					$e = $this->exception('Employee does not have post defined');
					if($this->for_model) $e->addMoreInfo('for_model',$this->for_model);
					throw $e;
				}
				if(!$m['type'] && $this->for_model){
					$e=  $this->exception('Type is not defined for '. $this->for_model);
					if($this->for_model) $e->addMoreInfo('for_model',$this->for_model);
					throw $e;
				}

				$e=  $this->exception('ACL Model does not have proper informations')
							// ->addMoreInfo('epan_id',$m['epan_id'])
							->addMoreInfo('post_id',$m['post_id'])
							->addMoreInfo('type',$m['type'])
							->addMoreInfo('namespace',$m['namespace']);
				if($this->for_model) $e->addMoreInfo('for_model',$this->for_model);

				throw $e;
			}
		});

		$this->addHook('afterLoad',function($m){
			$m['action_allowed'] = json_decode($m['action_allowed'],true);
		});
	}
}
