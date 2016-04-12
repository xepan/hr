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
	
	public $acl_m = null;
	public $action_allowed=[];
	public $permissive_acl=false;
	public $action_btn_group=null;

	function init(){
		parent::init();		
	
		if($this->app->auth->model['scope']=='SuperUser') $this->permissive_acl=true;

		$this->model = $model = $this->getModel();

		$this->canDo();

		// Put Model View Conditions 

		if($model instanceof \xepan\base\Model_Document){		
			$view_array = $this->canView();	

			$q= $this->model->dsql();

			$where_condition=[];
			foreach ($view_array as $status => $acl) { // acl can be true(for all, false for none and [] for employee created_by_ids)
				if($status=='All' || $status=='*'){
					if($acl === true) break;
					if($acl === false) $acl = -1; // Shuold never be reached
					$model->addCondition('created_by_id',$acl);
					break;
				}else{
					if($acl === false) continue;
					if($acl === true){
						// No employee condition .. just check status
						$where_condition[] = "([1] = \"$status\")";
					}else{
						$where_condition[] = "( ([0] in (". implode(",", $acl) .")) AND ([1] = \"$status\") )";
					}
				}
			}
			if(!empty($where_condition)){
				$this->model->addCondition(
					$q->expr("(".implode(" OR ", $where_condition).")", 
						[
							$this->model->getElement('created_by_id'), 	// [0]
							$this->model->getElement('status'),			// [2]
						]
						)
					)
					// ->debug()
				;
			}

		}

		// TODO Cross check hook for add/edit on Model, if trying to hack UI when not permitted
		// 
		// 
		// 			TODO
		// 
		// 

		// Check add/edit/delete if CRUD/Lister
		
		if(($crud = $this->getView()) instanceof \xepan\base\CRUD){			
			if(!$this->canAdd())
				$this->getView()->add_button->destroy();
			
			if(!$crud->isEditing()){
				$grid = $crud->grid;
				
				if($grid instanceof \xepan\base\Grid){
					$grid->addMethod('format_edit',function($g,$f){
						$ids= $this->canEdit($g->model['status']);
						if($ids === true || $ids == false){
							$g->row_edit=$ids;
							return;
						}
						if(!in_array($g->model['created_by_id'], $ids)){
							$g->row_edit = false;
						}
					});
					$grid->setFormatter('edit','edit');

					$grid->addMethod('format_delete2',function($g,$f){
						$ids= $this->canDelete($g->model['status']);
						if($ids === true || $ids == false){
							$g->row_delete=$ids;
							return;
						}
						if(!in_array($g->model['created_by_id'], $ids)){
							$g->row_delete = false;
							return;
						}

						$g->row_delete = true;
					});
					$grid->setFormatter('delete','delete2');
				}
			}
		}


		// Add Actions
		
		if($view = $this->getView()){
			if($view instanceof \xepan\base\CRUD){
				if(!$view->isEditing()){
					$view = $view->grid;
				}
			}

			if($view instanceof \Grid){
				$view->addColumn('template','action');
				$view->addMethod('format_action',function($g,$f){
					$actions = $this->getActions($g->model['status']);

					if(isset($actions['edit'])) unset($actions['edit']);
					if(isset($actions['view'])) unset($actions['view']);
					if(isset($actions['delete'])) unset($actions['delete']);
					
					$action_btn_list = [];
					foreach ($actions as $action => $acl) {
						if($acl===false) continue;
						if($acl === true){
							 $action_btn_list[] = $action;
							 continue;	
						}
						if(is_array($acl)){
							if(in_array($g->model['created_by_id'], $acl))
								$action_btn_list[] = $action;
						}
					}
					if(!isset($g->current_row_html['action']))
						$g->current_row_html['action']= $this->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$g->model->id,'status'=>$g->model['status'],'action_btn_group'=>$this->action_btn_group])->getHTML();
				});
				$view->setFormatter('action','action');
				$view->on('click','.acl-action',[$this,'manageAction']);

				$view->addColumn('template','attachment_icon');
				$view->addMethod('format_attachment_icon',function($g,$f){
					if($g->model['attachments_count'])
						$g->current_row_html[$f]='<i class="fa fa-paperclip fa-2x" style="color:green"></i> '.$g->model['attachments_count'];
					else
						$g->current_row_html[$f]='<i class="fa fa-paperclip" style="color:grey"></i>';
				});
				$view->setFormatter('attachment_icon','attachment_icon');


			}elseif($view instanceof \xepan\base\View_Document){

			}
			// May be you have CRUD form here
		}
	}

	function getModel(){
		return $this->owner instanceof \Model_Table ? $this->owner: $this->owner->model;
	}

	
	function getView(){
		return $this->owner instanceof \xepan\base\Model_Table ? $this->owner->owner: $this->owner;
	}


	function canView(){
		$view_array=[];

		foreach ($this->action_allowed as $status => $actions) {
			$view_array[$status] = isset($actions['view'])?$actions['view']:false;
		}


		return $view_array;
	}

	function canAdd(){
		return $this->api->auth->model->isSuperUser()?true:($this->acl_m['allow_add']===null?$this->permissive_acl:$this->acl_m['allow_add']);
	}

	function canEdit($status=null){
		if(!$status){
			$status='All';
		}
		return $this->action_allowed[$status]['edit']===null?$this->permissive_acl:$this->action_allowed[$status]['edit']; // can be true/false/ or []
	}

	function canDelete($status=null){
		if(!$status) $status='All';
		return $this->action_allowed[$status]['delete']===null?$this->permissive_acl:$this->action_allowed[$status]['delete']; // can be true/false/ or []
	}

	function getActions($status=null){
		if(!$status) $status='All';
		return $this->action_allowed[$status]?:[];
	}

	function canDoAction($action,$status=null){
		if(!$status) $status='All';
	}

	function manageAction($js,$data){		
		$this->model = $this->model->newInstance()->load($data['id']?:$this->api->stickyGET($this->name.'_id'));
		$action=$data['action']?:$this->api->stickyGET($this->name.'_action');
		if($this->model->hasMethod('page_'.$action)){
			$p = $this->add('VirtualPage');
			$p->set(function($p)use($action){
				try{
					$this->api->db->beginTransaction();
					$page_action_result = $this->model->{"page_".$action}($p);
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

				}catch(\Exception $e){
					$this->api->db->rollback();
					throw $e;
					
				}
				if($page_action_result){
					$p->js(true)->univ()->location();
				}
			});
			return $js->univ()->frameURL('Action',$this->api->url($p->getURL(),[$this->name.'_id'=>$data['id'],$this->name.'_action'=>$data['action']]));
		}elseif($this->model->hasMethod($action)){
			try{
					$this->api->db->beginTransaction();
					$this->model->$action();
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

				}catch(\Exception $e){
					$this->api->db->rollback();
					throw $e;
				}
			$this->getView()->js()->univ()->location()->execute();
		}else{
			return $js->univ()->errorMessage('Action "'.$action.'" not defined in Model');
		}
	}

	/**
	 * [canDo description]
	 * @return ['submit'=>[1],'can_view'=>false/true/[1,2,3]] [description]
	 */
	function canDo(){

		if(!isset($this->model->acl)){
			throw $this->exception('ACL property not set')
						->addMoreInfo('Model',get_class($this->model));
			
		}

		if($this->model->acl === false){
			
			$this->permissive_acl = true;
			return;
		}

		if(strpos($this->model->acl, 'xepan\\')===0){
			$this->model = $this->add($this->model->acl);
		}

		$class = new \ReflectionClass($this->model);
		$this->acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',$class->getNamespaceName())
					->addCondition('document_type',$this->model['type'])
					->addCondition('post_id',$this->app->employee['post_id'])
					;
		$this->acl_m->tryLoadAny();
		if(!$this->acl_m->loaded()){
			$this->acl_m['allow_add'] = $this->permissive_acl;
			$this->acl_m->save();
		}
		
		/**
		 * ACL
		 * -  post_id  
		 * -  document_type('Opportunity') 
		 * - actions_allowed (
		 * 'add'=>true/false,
		 * 'Draft' => [
			 * 	view=>'Self Only', 'All', 'None' ,'Subordinate etc'
			 ***** view => true/false/[23,21,32]
			 *  edit => 'Self Only',
			 ***** edit => true/false/[23,21,32]
			 *  delete => 'Self Only'
			 ***** delete => true/false/[23,12,32]
			 *  submit => 'Self Only'
			 ***** submit => true/false/[21,23,32]
			 * ]
		 * )
		 */
		$this->action_allowed = $this->acl_m['action_allowed'];

		foreach ($this->model->actions as $status => $actions) {
			foreach ($actions as $action) {
				$acl_value = isset($this->action_allowed[$status][$action])?$this->action_allowed[$status][$action]:$this->permissive_acl;
				$this->action_allowed[$status][$action] = $this->api->auth->model->isSuperUser()?true:$this->textToCode($acl_value);
			}
		}

		return $this->action_allowed;
	}

	function textToCode($text){
		if($text =='' || $text === null) return $this->permissive_acl;
		if($text == 'None') return false;
		if($text == 'All' || $text === true ) return true;
		if($text == 'Self Only') return [$this->app->employee->id];
	}
}
