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

class page_aclmanagement1 extends \xepan\base\Page {
	public $title='Access Control Management';

	function init(){
		parent::init();

		// if(!$this->api->auth->model->isSuperUser()){
		// 	$this->add('View_Error')->set('Sorry, you are not permitted to handle acl, Ask respective authority');
		// 	return;
		// }

		$post = $this->api->stickyGET('post_id');
		$ns = $this->api->stickyGET('namespace');
		$dt = $this->api->stickyGET('type');

		$acl_m = $this->add('xepan\hr\Model_ACL');
		$acl_m->_dsql()->group('name');

		// $acl_m->add('xepan\hr\Controller_ACL');

		$post_m = $this->add('xepan\hr\Model_Post');
		$post_m->addExpression('post_with_department')->set($post_m->dsql()->expr('CONCAT([0]," : ",[1])',[$post_m->getElement('department'),$post_m->getElement('name')]));
		$post_m->title_field = 'post_with_department';

		$form = $this->add('Form',null,null,['form/empty']);
		$form->setLayout('form/aclpost');

		$form->addField('DropDown','post')
									->addClass('form-control')
									->setEmptyText('Please Select')
									->validate('required')
									->setModel($post_m)->set($post);
									
		$form->addField('DropDown','type')
									->addClass('form-control')
									->setEmptyText("All Entities")
									->setModel($acl_m);
									;

		$form->addSubmit('Go')->addClass('btn btn-success');

		$af = $this->add('Form');


		if($dt){
			$is_config= false;
			try{
				$m = $this->add($ns.'\\Model_'.$dt);
			}catch(\Exception $e){
				try{
					$m = $this->add($ns.'\\'.$dt);
				}catch(\Exception $e1){
					$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
					$is_config = true;
				}
			}

			$existing_acl = $this->add('xepan\hr\Model_ACL')
								->addCondition('post_id',$post)
								->addCondition('namespace',$ns)
								->addCondition('type',$dt)
								->tryLoadAny();

			if(!$existing_acl->loaded())
				$existing_acl->save();

			if(!$is_config){
				$af->addField('Checkbox','allow_add');
				$af->getElement('allow_add')->set($existing_acl['allow_add']);
			}
			

			$value_list=['Self Only'=>'Self Only','All'=>'All','None'=>'None'];
			
			if(isset($m->assigable_by_field) && $m->assigable_by_field !=false)
				$value_list['Assigned To']='Assigned To Employee';

			if($is_config){
				unset($value_list['Self Only']);
			}

			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;	
				$greeting_card = $af->add('View', null, null, ['view/acllist']);
				foreach ($actions as $action) {
					$greeting_card->template->set('action',$status);					
					$greeting_card->addField('DropDown',$status.'_'.$action,$action)
						->setValueList($value_list)
						->setEmptyText('Please select ACL')
						->validate('required?Acl must be provided or will work based on global permisible setting')
						->addClass('form-control')
						->set($existing_acl['action_allowed'][$status][$action]);
					;
				}
			}
			$af->addSubmit('Update')->addClass('btn btn-success');
		}

		$af->onSubmit(function($f)use($post,$ns,$dt){
			$is_config = false;
			try{
				$m = $this->add($ns.'\\Model_'.$dt);
			}catch(\Exception $e){
				try{
					$m = $this->add($ns.'\\'.$dt);
				}catch(\Exception $e1){
					$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
					$is_config = true;
				}
			}
			$acl_array=[];
			foreach ($m->actions as $status => $actions) {
				$status = $status=='*'?'All':$status;			
				foreach ($actions as $action) {
					$acl_array[$status][$action] = $f[$this->api->normalizeName($status.'_'.$action)];
				}
			}

			$class = new \ReflectionClass($m);
			$acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',isset($ns)? $ns:$class->getNamespaceName());
			
			if($m['type']=='Contact' || $m['type']=='Document')
				$m['type'] = str_replace("Model_", '', $class->getShortName());

			$acl_m->addCondition('type',$m['type']?:$m->acl_type);
			$acl_m->addCondition('post_id',$post)
					;
			$acl_m->tryLoadAny();
			$acl_m['action_allowed'] = json_encode($acl_array);
			$acl_m['allow_add'] = $f['allow_add']?:false;
			$acl_m->save();
			return "Done";
		});

		$form->onSubmit(function($f)use($af){
			$acl_m = $this->add('xepan\hr\Model_ACL')->load($f['type']);
			return $af->js()->reload(['post_id'=>$f['post'],'namespace'=>$acl_m['namespace'],'type'=> $acl_m['type']]);
		});
		
	}

	function defaultTemplate(){
		return ['page/aclmanagement'];
	}
}
