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
	public $action_allowed=null;
	public $permissive_acl=false;
	public $action_btn_group=null;
	public $view_reload_url=null;
	public $dependent = false;

	function init(){
		parent::init();
		
		if(isset($this->app->muteACL) && $this->app->muteACL) return; 
		
		if($this->app->getConfig('all_rights_to_superuser',true) && $this->app->auth->model['scope']=='SuperUser') $this->permissive_acl=true;

		$this->model = $model = $this->getModel();

		$this->canDo();

		$this->view_reload_url = $this->app->url(null,['cut_object'=>$this->getView()->name]);

		// Put Model View Conditions 

		if($model instanceof \Model){
			$view_array = $this->canView();	

			$where_condition=[];
			foreach ($view_array as $status => $acl) { // acl can be true(for all, false for none and [] for employee created_by_ids)

				if($status=='All' || $status=='*'){
					if($acl === true) break;
					if($acl === false) $acl = -1; // Shuold never be reached
					$model->addCondition('created_by_id',$acl);
					break;
				}else{
					if($acl==="All") continue;
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
				$q= $this->model->dsql();
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
		if(!$this->canAdd($this->model['status'])){
			$this->model->addHook('beforeInsert',function($m){
				throw $this->exception('You are not permitted to add '. ucfirst($this->model->table));
			});
		}

		if(!$this->canEdit($this->model['status'])){
			$this->model->addHook('beforeSave',function($m){
				throw $this->exception('You are not permitted to edit '. ucfirst($this->model->table))
						->addMoreInfo('Model',$this->model)
						->addMoreInfo('Permissions',print_r($this->action_allowed,true))
						->addMoreInfo('model_id',$this->model->id)
						->addMoreInfo('status',$this->model['status'])
						->addMoreInfo('status_edit_permission',$this->action_allowed[$this->model['status']]['edit'])
						->addMoreInfo('model_created_by',$this->model['created_by_id'])
						->addMoreInfo('app->amployee->id',$this->app->employee->id)
						;
			});
		}

		if(!$this->canDelete($this->model['status'])){
			$this->model->addHook('beforeDelete',function($m){
				throw $this->exception('You are not permitted to delete '. ucfirst($this->model->table). ' ['. $this->model[$this->model->title_field] .']');
			});
		}

		// Check add/edit/delete if CRUD/Lister
		
		if(($crud = $this->getView()) instanceof \xepan\base\CRUD){
			if(!$this->canAdd() && $this->getView()->add_button)
				$this->getView()->add_button->destroy();
			
			if(!$crud->isEditing()){
				$grid = $crud->grid;
				
				if($grid instanceof \xepan\base\Grid){
					$grid->addMethod('format_edit',function($g,$f){
						if($this->dependent){
							$ids= $this->canEdit($g->model['status']?:$this->model['status']);
						}else{
							$ids= $this->canEdit($g->model['status']);
						}
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
						if($this->dependent){
							$ids= $this->canDelete($g->model['status']?:$this->model['status']);
						}else{
							$ids= $this->canDelete($g->model['status']);
						}
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
				if(!isset($this->app->acl_action_added[$view->name])){
					$view->on('click','.acl-action',[$this,'manageAction']);
					$this->app->acl_action_added[$view->name] = true;
				}

				$view->addColumn('template','attachment_icon');
				$view->addMethod('format_attachment_icon',function($g,$f){
					if($g->model['attachments_count'])
						$g->current_row_html[$f]='<i class="fa fa-paperclip fa-2x" style="color:green"></i> '.$g->model['attachments_count'];
					else
						$g->current_row_html[$f]='<i class="fa fa-paperclip" style="color:grey"></i>';
				});
				$view->setFormatter('attachment_icon','attachment_icon');


			}elseif($view instanceof \View){
				if(!isset($view->effective_template))
					$view->effective_template=$view->template;
				if($view->effective_template->hasTag('action') && $this->model->loaded()){
					$actions = $this->getActions($this->model['status']);
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
							if(in_array($this->model['created_by_id'], $acl))
								$action_btn_list[] = $action;
						}
					}

					$view->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$this->model->id,'status'=>$this->model['status'],'action_btn_group'=>'xs'],'action')->getHTML();
					if(!isset($this->app->acl_action_added[$view->name])){
						if(!isset($this->app->acl_action_added[$view->name]))
							$view->effective_object=$view;
						$view->effective_object->on('click','.acl-action',[$this,'manageAction']);
						$this->app->acl_action_added[$view->name] = true;
					}
				}

			}
			// May be you have CRUD form here
		}
	}

	function getModel(){
		$model =  $this->owner instanceof \Model ? $this->owner: $this->owner->model;		
		if(strpos($model->acl, 'xepan\\')===0 or $model->acl instanceof \Model){
			if(is_string($model->acl)){
				$this->dependent=$model;
				$model = $this->add($model->acl);
			}else{
				$model = $model->acl;
			}
		}

		return $model;
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
		return ($this->api->auth->model->isSuperUser() && $this->app->getConfig('all_rights_to_superuser',true))?true:($this->acl_m['allow_add']===null?$this->permissive_acl:$this->acl_m['allow_add']);
	}

	function canEdit($status=null){
		if(!$status){
			$status='All';
		}
		$x =  $this->action_allowed[$status]['edit']===null?$this->permissive_acl: $this->action_allowed[$status]['edit']?: in_array($this->app->employee->id,$this->action_allowed[$status]['edit']?:[]); // can be true/false/ or []
		return $x;
	}

	function canDelete($status=null){
		if(!$status) $status='All';
		return $this->action_allowed[$status]['delete']===null?$this->permissive_acl: $this->action_allowed[$status]['delete']?: in_array($this->app->employee->id,$this->action_allowed[$status]['delete']?:[]); // can be true/false/ or []
	}

	function getActions($status=null){
		if(!$status) $status='All';
		return $this->action_allowed[$status]?:[];
	}

	function canDoAction($action,$status=null){
		if(!$status) $status='All';
	}

	function manageAction($js,$data){		
		$this->app->inAction=true;
		$this->model = $this->model->newInstance()->load($data['id']?:$this->api->stickyGET($this->name.'_id'));
		$action=$data['action']?:$this->api->stickyGET($this->name.'_action');
		if($this->model->hasMethod('page_'.$action)){
			$p = $this->add('VirtualPage');
			$p->set(function($p)use($action){
				try{
					$this->api->db->beginTransaction();
						$page_action_result = $this->model->{"page_".$action}($p);						
					
					if($this->app->db->intransaction()) $this->api->db->commit();

				}catch(\Exception_StopInit $e){
					if($this->app->db->intransaction()) $this->api->db->commit();

				}catch(\Exception $e){
					if($this->app->db->intransaction()) $this->api->db->rollback();
					throw $e;
				}
				if(isset($page_action_result) or isset($this->app->page_action_result)){
					
					if(isset($this->app->page_action_result)){						
						$page_action_result = $this->app->page_action_result;
					}

					$js=[];
					if($page_action_result instanceof \jQuery_Chain) {
						$js[] = $page_action_result;
					}
					$js[]=$this->getView()->js()->univ()->closeDialog();
					$js[]= $this->getView()->js()->reload(null,null,$this->view_reload_url);
					
					$this->getView()->js(null,$js)->execute();
					// $p->js(true)->univ()->location();
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
			$this->getView()->js()->reload(null,null,$this->view_reload_url)->execute();
			// $this->getView()->js()->univ()->location()->execute();
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


		$class = new \ReflectionClass($this->model);

		// if($this->model->acl !==true && $this->model->acl !==false && $this->model->acl !== null){
		// 	$ns = $this->model->acl;
		// }else{
		// 	$ns = $class->getNamespaceName();
		// }


		$this->acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',isset($this->model->namespace)? $this->model->namespace:$class->getNamespaceName());

		if($this->model['type']=='Contact' || $this->model['type']=='Document')
				$this->model['type'] = str_replace("Model_", '', $class->getShortName());
		
		$this->acl_m->addCondition('type',isset($this->model->acl_type)?$this->model->acl_type:$this->model['type']);
		$this->acl_m->addCondition('post_id',$this->app->employee['post_id']);
		
		$this->acl_m->tryLoadAny();
		if(!$this->acl_m->loaded()){
			$this->acl_m['allow_add'] = $this->permissive_acl;
			$this->acl_m->save();
		}
		
		/**
		 * ACL
		 * -  post_id  
		 * -  type('Opportunity') 
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
		// echo $this->model. '<br/>';
		if($this->action_allowed===null)
			$this->action_allowed = $this->acl_m['action_allowed'];
		// echo "acl_data";
		// var_dump($this->permissive_acl);
		// exit;
		foreach ($this->model->actions as $status => $actions) {
			if($status=='*') $status='All';
			foreach ($actions as $action) {
				$acl_value = isset($this->action_allowed[$status][$action])?$this->action_allowed[$status][$action]:$this->permissive_acl;
				// echo " testing $action in $status as $acl_value : " . $this->textToCode($acl_value). ' <br/>';
				$this->action_allowed[$status][$action] = ($this->api->auth->model->isSuperUser() && $this->app->getConfig('all_rights_to_superuser',true))?true:$this->textToCode($acl_value);
			}
		}		

		// remove actions tht was in acl but now model has updated
		// echo "acl_data converted to ids array";
		// var_dump($this->action_allowed);
		// exit;
		// echo "model->actions";
		// var_dump($this->model->actions);

		foreach ($this->acl_m['action_allowed'] as $status => $actions_array) {
			if(!isset($this->model->actions[$status])){
				// echo 'unsetting $this->model->actions['.$status.'] <br/>';
				unset($this->action_allowed[$status]);
				continue;
			}
			foreach ($actions_array as $action=>$permission) {
				if(!in_array($action,$this->model->actions[$status])){
					// echo "in_array($action, ".print_r($this->model->actions[$status], true).' for '.$status.' -- unsetting 2 '. $action .' $this->model->actions['.$status.']['.$action.'] <br/>';
					unset($this->action_allowed[$status][$action]);
				}
			}

		}

		// echo "final this->action_allowed";
		// var_dump($this->action_allowed);
		// die('');

		return $this->action_allowed;
	}

	function textToCode($text){
		if($text ==='' || $text === null) return $this->permissive_acl;
		if($text === 'None') return false;
		if($text === 'All' || $text === true ) return true;
		if($text === 'Self Only') return [$this->app->employee->id];
		return $text;
	}
}
