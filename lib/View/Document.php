<?php

/**
* description: View_Document is special View that helps to edit 
* any View in its own template by using same template as form layout
* It also helps in managing hasMany relations to be Viewed and Edit on same Level
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class View_Document extends \xepan\base\View_Document{

	function setModel($model,$view_fields=null,$form_fields=null){
		$m = parent::setModel($model,$view_fields,$form_fields);

		if((($m instanceof \xepan\base\Model_Document) || ($m instanceof \xepan\base\Model_Contact)) && !$this->pass_acl){
			$this->add('xepan\hr\Controller_ACL');
		}
		return $m;
	}

	function addMany(
			$model,
			$view_class='xepan\base\Grid',$view_options=null,$view_spot='Content',$view_defaultTemplate=null,$view_fields=null,
			$class='xepan\base\CRUD',$options=null,$spot='Content',$defaultTemplate=null,$fields=null
		)
	{

	$v = parent::addMany(
			$model,
			$view_class,$view_options,$view_spot,$view_defaultTemplate,$view_fields,
			$class,$options,$spot,$defaultTemplate,$fields
			);

	$m = $v->model;
	if((($m instanceof \xepan\base\Model_Document) || ($m instanceof \xepan\base\Model_Contact)) && !$this->pass_acl){
			$this->add('xepan\hr\Controller_ACL');
		}
		return $m;


	return $v;
	}

	function recursiveRender(){

		if($this->action != 'view') {
			$this->form->onSubmit(function($f){	
				$f->save();
				return $this->js(null,$this->js()->univ()->notify('user','Saved','attached','bouncyflip'))->reload(['id'=>$f->model->id,'action'=>($this->action=='add'?'edit':$this->action)]);
				return $js;
			});	
		}

		return parent::recursiveRender();
	}


}
