<?php

/**
* description: xEpan ACL Controller, Realtion between document and contact
* Still confused if it should be in HR or Here. Must not mix multiple applications anyhow.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Controller_ACL extends \AbstractController {

	function init(){
		parent::init();


		// 
		// filter model-data that user can see
		// manage add/edit/delete button on crud (self only or all)
		// check if trying to save (by hack URL) when not permitted on View_Document type view
		// Check GET action if not permitted block owner view
		// add actions to owner grid template
		// or on view template at action spot
		// 
		// also check if model is Document need other actions
		// while for COntact its should be just add/edit/delete
		// 
		// If Owner -> Model {
		// 	put -> can_view condition
		// 
		// }
	
		$model =$this->getModel();		

		$acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('document_type',$model['type'])
					->addCondition('post_id',$this->app->employee['post_id'])
					->addCondition('status',$model['status'])
					;

		$acl_m->tryLoadAny();
		
		if(!$acl_m->loaded()) $acl_m->save();

		

		// if(!$this->owner->isEditing()){
		// 	$this->owner->grid->addMethod('format_edit',function($g,$f){
		// 		$g->row_edit =false;				
		// 	});
		// 	$this->owner->grid->setFormatter('edit','edit');
		// }
		
		$this->owner->grid->addColumn('template','action')->setTemplate('<div class="btn-group">
<button type="button" class="btn btn-primary">Action</button>
<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
<span class="caret"></span>
</button>
<ul class="dropdown-menu" role="menu">
<li><a href="#">Action</a></li>
<li><a href="#">Another action</a></li>
<li><a href="#">Something else here</a></li>
<li class="divider"></li>
<li><a href="#">Separated link</a></li>
</ul>
</div>');

	}

	function getModel(){
		return $this->owner instanceof \Model_Table ? $this->owner: $this->owner->model;
	}

	
	function isCrud(){
		return $this->owner instanceof \CRUD ? $this->owner: false;
	}

	function getLister(){
		return $this->getModel()->owner;
	}

	function canView(){

	}
}
