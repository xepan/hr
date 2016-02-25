<?php

/**
* description: AclManagement page is responsible to define ACL for Post and Documents
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_aclmanagement extends \Page {
	public $title='Access Control Management';

	function init(){
		parent::init();

		$post = $this->api->stickyGET('post_id');
		$dt = $this->api->stickyGET('document_type');

		$form = $this->add('Form',null,null,['form/empty']);
		$form->setLayout('form/aclpost');

		$form->addField('DropDown','post')->addClass('form-control')->setModel('xepan\hr\Post')->set($post);
		$form->addField('DropDown','document_type')
									->addClass('form-control')
									->setValueList([
										'xepan\commerce\Model_Item'=>'xepan\commerec\Model_Item',
										'xepan\commerce\Model_Category'=>'xepan\commerec\Model_Category',
										'xepan\commerce\Model_Quotation'=>'xepan\commerec\Model_Quotation'
										])
									->set($dt);

		$form->addSubmit('Go')->addClass('btn btn-success');

		$af = $this->add('Form');

		if($dt){
			$m = $this->add($dt);
			foreach ($m->actions as $status => $actions) {
				$greeting_card = $af->add('View', null, null, ['view/acllist']);
				foreach ($actions as $action) {
					$greeting_card->template->set('action',$status);
					$greeting_card->addField('DropDown',$status.'_'.$action,$action)
						->setValueList(['Self Only'=>'Self Only','All'=>'All','None'=>'None'])->addClass('form-control');
					;
				}
			}
			$af->addSubmit('Update')->addClass('btn btn-success');
		}

		$af->onSubmit(function($f)use($post,$dt){
			$m = $this->add($dt);
			$acl_array=[];
			foreach ($m->actions as $status => $actions) {				
				foreach ($actions as $action) {
					$acl_array[$status][$action] = $f[$this->api->normalizeName($status.'_'.$action)];
				}
			}

			$class = new \ReflectionClass($m);
			$acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',$class->getNamespaceName())
					->addCondition('document_type',$m['type'])
					->addCondition('post_id',$post)
					;
			$acl_m->tryLoadAny();
			$acl_m['action_allowed'] = json_encode($acl_array);

			$acl_m->save();

		});

		$form->onSubmit(function($f)use($af){
			return $af->js()->reload(['post_id'=>$f['post'],'document_type'=>$f['document_type']]);
		});
		
	}

	function defaultTemplate(){
		return ['page/aclmanagement'];
	}
}
