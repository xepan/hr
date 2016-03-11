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

		if(!$this->api->auth->model->isSuperUser()){
			$this->add('View_Error')->set('Sorry, you are not permitted to handle acl, Ask respective authority');
			return;
		}

		$post = $this->api->stickyGET('post_id');
		$ns = $this->api->stickyGET('namespace');
		$dt = $this->api->stickyGET('document_type');

		$acl_m = $this->add('xepan\hr\Model_ACL');

		$form = $this->add('Form',null,null,['form/empty']);
		$form->setLayout('form/aclpost');

		$form->addField('DropDown','post')->addClass('form-control')->setModel('xepan\hr\Post')->set($post);
		$form->addField('DropDown','document_type')
									->addClass('form-control')
									->setModel($acl_m)
									->_dsql()->del('fields')->field($this->api->db->dsql()->expr('distinct([0]',[$acl_m->getElement('name')]));

		$form->addSubmit('Go')->addClass('btn btn-success');

		$af = $this->add('Form');


		if($dt){
			$af->addField('Checkbox','allow_add');
			$m = $this->add($ns.'\\Model_'.$dt);
			$existing_acl = $this->add('xepan\hr\Model_ACL')
								->addCondition('post_id',$post)
								->addCondition('namespace',$ns)
								->addCondition('document_type',$dt)
								->loadAny();

			$af->getElement('allow_add')->set($existing_acl['allow_add']);
			
			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;	
				$greeting_card = $af->add('View', null, null, ['view/acllist']);
				foreach ($actions as $action) {
					$greeting_card->template->set('action',$status);					
					$greeting_card->addField('DropDown',$status.'_'.$action,$action)
						->setValueList(['Self Only'=>'Self Only','All'=>'All','None'=>'None'])
						->addClass('form-control')
						->set($existing_acl['action_allowed'][$status][$action]);
					;
				}
			}
			$af->addSubmit('Update')->addClass('btn btn-success');
		}

		$af->onSubmit(function($f)use($post,$ns,$dt){
			$m = $this->add($ns.'\\Model_'.$dt);
			$acl_array=[];
			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;			
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
			$acl_m['allow_add'] = $f['allow_add'];
			$acl_m->save();

		});

		$form->onSubmit(function($f)use($af){
			$acl_m = $this->add('xepan\hr\Model_ACL')->load($f['document_type']);
			return $af->js()->reload(['post_id'=>$f['post'],'namespace'=>$acl_m['namespace'],'document_type'=> $acl_m['document_type']]);
		});
		
	}

	function defaultTemplate(){
		return ['page/aclmanagement'];
	}
}
