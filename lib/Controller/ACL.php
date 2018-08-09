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
	public $action_allowed=null;  // final array of employees ids in [status][action] key
	public $action_allowed_raw= []; // raw text in [status][action] like 'Self Only' or "NO" or "Assigned To"
	public $permissive_acl=false;
	public $action_btn_group=null;
	public $view_reload_url=null;
	public $dependent = false;

	public $add_actions=true;
	public $skip_allow_add=false;

	public $based_on_model=null;
	public $based_on_view=null;

	public $model_class=null;
	public $model_ns = null;
	public $status_color = [];

	public $show_spot_acl=true;

	public $entity_list=[];

	public $debug=false;

	public $skip_branch = false;

	function init(){
		parent::init();
		
		if(!$this->app->epan->isApplicationInstalled('xepan\hr')) return;
		if((isset($this->app->muteACL) && $this->app->muteACL) || isset($this->app->actionsWithoutACL) ) return; 

		
		if($this->app->getConfig('all_rights_to_superuser',true) && $this->app->auth->model['scope']=='SuperUser') $this->permissive_acl=true;

		$this->model = $model = $this->getModel();

		$this->model_class = new \ReflectionClass($this->model);
		$this->model_ns = $this->model->namespace?:$this->model_class->getNamespaceName();
		
		if($this->app->immediateAppove($this->model_ns)) $this->permissive_acl = true;

		$this->canDo();

		$this->view_reload_url = $this->app->url(null,['cut_object'=>$this->getView()->name]);

		// Put Model View Conditions 

		if($model instanceof \Model){
			$view_array = $this->canView();

			if($this->isBranchRestricted()){
				$model->addCondition('branch_id',@$this->app->branch->id);
			}

			$a=[];
			foreach ($view_array as $status => $acl) { // acl can be true(for all, false for none and [] for employee created_by_ids)

				if($status=='All' || $status=='*'){
					if($acl === true) break;
					if($acl === false) $acl = -1; // Shuold never be reached
					$model->addCondition($this->getConditionalField($status,'view'),$acl);
					break;
				}else{
					if($acl==="All") continue;
					if($acl === false){
						$where_condition[] = "(false)";
						continue;
					}
					if($acl === true){
						// No employee condition .. just check status
						$where_condition[] = "([status] = \"$status\")";
					}else{
						$where_condition[] = "( ([".strtolower($status)."] in (". implode(",", $acl) .")) AND ([status] = \"$status\") )";
					}
				}
			}
			if(!empty($where_condition)){
				$q= $this->model->dsql();
				
				$filler_values=['status'=>$this->model->getElement('status')];
				foreach ($this->action_allowed_raw as $status => $actions) {
					$filler_values[strtolower($status)]=$this->model->getElement($this->getConditionalField($status,'view'));
				}

				$this->model->addCondition(
					$q->expr("(".implode(" OR ", $where_condition).")", 
							$filler_values
						)
					)
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
			if(!$this->canAdd() && $this->getView()->add_button){				
				$this->getView()->add_button->destroy();
				$this->getView()->add_button = null;
			}
			
			if(!$crud->isEditing()){
				$grid = $crud->grid;
				
				if($grid instanceof \xepan\base\Grid){
					$grid->addMethod('format_edit',function($g,$f){
						if($g->row_edit === false) return;
						if($this->dependent){
							$ids= $this->canEdit($g->model['status']?:$this->model['status']);
						}else{
							$ids= $this->canEdit($g->model['status']);
						}
						if($ids === true || $ids == false){
							$g->row_edit=$ids;
							return;
						}
						if(!in_array($g->model[$this->getConditionalField($g->model['status'],'edit')], $ids)){
							$g->row_edit = false;
						}
					});
					if($grid->hasColumn('edit')){
						$grid->setFormatter('edit','edit');
					}

					$grid->addMethod('format_delete2',function($g,$f){
						if($g->row_delete === false) return;
						if($this->dependent){
							$ids= $this->canDelete($g->model['status']?:$this->model['status']);
						}else{
							$ids= $this->canDelete($g->model['status']);
						}
						if($ids === true || $ids == false){
							$g->row_delete=$ids;
							return;
						}
						if(!in_array($g->model[$this->getConditionalField($g->model['status'],'delete')], $ids)){
							$g->row_delete = false;
							return;
						}

						$g->row_delete = true;
					});

					if($grid->hasColumn('delete')){
						$grid->setFormatter('delete','delete2');
					}
				}
			}
		}


		// Add Actions
		
		if(($view = $this->getView()) && $this->add_actions){
			if($view instanceof \xepan\base\CRUD){
				if(!$view->isEditing()){
					$view = $view->grid;
				}
			}

			if($view instanceof \Grid){
				$view->addColumn('template','action');
				$view->addMethod('format_action',function($g,$f){
					$actions = $this->getActions($g->model['status']);

					// if(isset($actions['edit'])) unset($actions['edit']);
					if(isset($actions['view'])) unset($actions['view']);
					// if(isset($actions['delete'])) unset($actions['delete']);
					
					$action_btn_list = [];
					foreach ($actions as $action => $acl) {
						if($acl===false) continue;
						if($acl === true){
							 $action_btn_list[] = $action;
							 continue;	
						}
						if(is_array($acl)){
							if(in_array($g->model[$this->getConditionalField($g->model['status'],$action)], $acl))
								$action_btn_list[] = $action;
						}
					}
					if(!isset($g->current_row_html['action']))
						$g->current_row_html['action'] = $this->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$g->model->id,'status'=>$g->model['status']?:"All",'action_btn_group'=>$this->action_btn_group,'status_color'=>$this->status_color])->getHTML();

				});
				$view->setFormatter('action','action');
				if(!isset($this->app->acl_action_added[$view->name])){
					$view->on('click','.acl-action:not(".pb_edit"):not(".do-delete")',[$this,'manageAction']);
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
				
				if($view->model['status']){
					$view->removeColumn('edit');
					$view->removeColumn('delete');	
				}


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
							// if(in_array($this->model['created_by_id'], $acl))  // Replaced line, not tested, in case if there is error just sap again
							if(in_array($this->model[$this->getConditionalField($this->model['status'],$action)], $acl))
								$action_btn_list[] = $action;
						}
					}

					$view->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$this->model->id,'status'=>$this->model['status'],'action_btn_group'=>'xs','status_color'=>$this->status_color],'action')->getHTML();
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

		// Show error on top if ACL is not managed or exported for current model

		$string_count = strpos($this->model_class, '\Model_');
		$model_namespace = substr($this->model_class,0,$string_count);
		$str = ($this->model->acl_type?$this->model->acl_type:$this->model['type']);
		$this->entity_list=[];
		$this->app->hook('entity_collection',[&$this->entity_list]);
		if(!array_key_exists($str, $this->entity_list) and $this->model->acl !== false){
			$this->acl_error_vp = $this->add('VirtualPage');
			$this->manageAclErrorVP($this->acl_error_vp);
			$view = $this->getView();

			if($view instanceof \CRUD){
				$view= $view->grid;
			}

			$spot = null;
			// if($view->template->hasTag('action')) $spot ='action';

			if($view instanceof \Grid){
				$spot='grid_buttons';
			}

			if(!($view instanceof \Dummy) and $this->model->acl !== false and ($view instanceof \View)){
				if($spot OR $view->template->hasTag('Content')){
					if(!($view instanceof \xepan\base\View_Document) OR $view->effective_template->hasTag('Content')){
						$btn = $view->add('Button',null,$spot)->set('ACL')->addClass('btn btn-danger');
						$btn->js('click')->univ()->frameURL($this->acl_error_vp->getURL());
					}
				}
			}
			
			$acl_error=true;
		}

		// add spot acl 
		if($this->show_spot_acl AND !isset($acl_error) AND $this->app->auth->model->isSuperUser()) {
			$this->spot_vp = $this->add('VirtualPage');
			$this->manageSpotACLVP($this->spot_vp);

			$view = $this->getView();


			if($view instanceof \CRUD){
				$view= $view->grid;
			}
			$spot = null;

			if($view instanceof \Grid){
				$spot='grid_buttons';
			}

			if(!($view instanceof \Dummy) and $this->model->acl !== false and ($view instanceof \View)){
				if($spot OR $view->template->hasTag('Content')){
					if(!($view instanceof \xepan\base\View_Document) OR $view->effective_template->hasTag('Content')){
						$btn=$view->add('Button',['name'=>$view->name."_acl"],$spot)->set('ACL')->addClass('btn btn-primary');
						$btn->js('click')->univ()->frameURL($this->spot_vp->getURL());
					}
				}
			}

		}


	}

	function getModel(){
		if($this->based_on_model){
			$model=$this->add($this->based_on_model);
		}else{
			$model =  $this->owner instanceof \Model ? $this->owner: $this->owner->model;
		}
		// model->acl property contain '\' to define namespace\model as base acl then add that 
		if(strpos($model->acl, '\\')!==false or $model->acl instanceof \Model){
		// if(is_string($model->acl) or $model->acl instanceof \Model){
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
		return $this->owner instanceof \Model ? $this->owner->owner: $this->owner;
	}

	function isBranchRestricted(){
		return (($this->acl_m['is_branch_restricted']?:false) AND $this->model->hasElement('branch_id'));
		// return (
		// 	($this->app->ACLModel === 'Departmental' &&  $this->app->immediateAppove($this->model_ns))
		// 	||
		// 	$this->app->ACLModel ==='none'
		// )?true:($this->acl_m['is_branch_restricted']===null?$this->permissive_acl:$this->acl_m['is_branch_restricted']);
	}

	function canView(){
		$view_array=[];

		foreach ($this->action_allowed as $status => $actions) {
			$view_array[$status] = isset($actions['view'])?$actions['view']:false;
		}

		return $view_array;
	}

	function canAdd(){
		return (
					($this->app->ACLModel === 'Departmental' &&  $this->app->immediateAppove($this->model_ns))
					||
					$this->app->ACLModel ==='none'
				)?true:($this->acl_m['allow_add']===null?$this->permissive_acl:$this->acl_m['allow_add']);
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
		$this->model = $this->model->newInstance()->load($this->api->stickyGET($this->name.'_id')?:$data['id']);
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
					$js[]=$p->js()->univ()->closeDialog();
					$js[]= $this->getView()->js()->reload(null,null,$this->view_reload_url);
					
					$this->getView()->js(null,$js)->execute();
					// $p->js(true)->univ()->location();
				}
			});
			return $js->univ()->frameURL('Action',$this->api->url($p->getURL(),[$this->name.'_id'=>$data['id'],$this->name.'_action'=>$data['action']]));
		}elseif($this->model->hasMethod($action)){
			try{
					$this->api->db->beginTransaction();
					$result_js = $this->model->$action();
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

				}catch(\Exception $e){
					$this->api->db->rollback();
					throw $e;
				}
				$js=[];
				if($result_js instanceof \jQuery_Chain) {
					$js[] = $result_js;
				}
			$this->getView()->js(null,$js)->reload(null,null,$this->view_reload_url)->execute();
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
			if($this->action_allowed===null) $this->action_allowed=[];
			$this->permissive_acl = true;
			return;
		}


		$class = new \ReflectionClass($this->model);

		// if($this->model->acl !==true && $this->model->acl !==false && $this->model->acl !== null){
		// 	$ns = $this->model->acl;
		// }else{
		// 	$ns = $class->getNamespaceName();
		// }


		$this->acl_m = $this->add('xepan\hr\Model_ACL',['for_model'=>$this->model])
					->addCondition('namespace',isset($this->model->namespace)? $this->model->namespace:$class->getNamespaceName());

		if($this->model['type']=='Contact' || $this->model['type']=='Document')
				$this->model['type'] = str_replace("Model_", '', $class->getShortName());
		
		$this->acl_m->addCondition('type',isset($this->model->acl_type)?$this->model->acl_type:$this->model['type']);
		$this->acl_m->addCondition('post_id',$this->app->employee['post_id']);

		if($this->debug)
			$this->acl_m->debug();

		$this->acl_m->tryLoadAny();
		if(!$this->acl_m->loaded()){
			$this->acl_m['allow_add'] = $this->permissive_acl;
			$this->acl_m->save();
		}

		if($this->debug){
			echo '$this->acl_m->id = ' .$this->acl_m->id. '<br/>';
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
		if($this->action_allowed===null){
			$this->action_allowed = $this->acl_m['action_allowed'];
			$this->action_allowed_raw = $this->acl_m['action_allowed'];
		}
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
				unset($this->action_allowed_raw[$status]);
				continue;
			}
			foreach ($actions_array as $action=>$permission) {
				if(!in_array($action,$this->model->actions[$status])){
					// echo "in_array($action, ".print_r($this->model->actions[$status], true).' for '.$status.' -- unsetting 2 '. $action .' $this->model->actions['.$status.']['.$action.'] <br/>';
					unset($this->action_allowed[$status][$action]);
					unset($this->action_allowed_raw[$status][$action]);
				}
			}

		}

		// echo "final this->action_allowed";
		// var_dump($this->action_allowed);
		// die('');

		return $this->action_allowed;
	}

	function getConditionalField($status,$action){
		if($this->action_allowed_raw[$status][$action] != 'Assigned To') return 'created_by_id';
		return isset($this->model->assigable_by_field)? $this->model->assigable_by_field: 'assigned_to_id';

	}

	function textToCode($text){
		// if config to department
		
			// if $ns is allowed to me return [$this->app->employee->id];
				// else
						// return false;
		if($this->app->ACLModel ==='Departmental' &&  !$this->app->immediateAppove($this->model_ns)){	
			return false;	
		} 

		if($text ==='' || $text === null) return $this->permissive_acl;
		if($text === 'None') return false;
		if($text === 'All' || $text === true ) return true;
		if($text === 'Self Only') return [$this->app->employee->id];
		if($text === 'Assigned To') return [$this->app->employee->id];
		return $text;
	}


	function manageSpotACLVP($vp){
		$vp->set(function($page){

			if($this->app->ACLModel === "Departmental"){
				$page->add('View')->addClass('alert alert-danger')->set('ACL is defined as Department Base, Please configure ACL from HR -> ACL menu');
				return;
			}

			$post = $this->api->stickyGET('post_id');
			$ns = $this->api->stickyGET('namespace');
			$dt = $this->api->stickyGET('type');

			$post_m = $page->add('xepan\hr\Model_Post');
			$post_m->addExpression('post_with_department')->set($post_m->dsql()->expr('CONCAT([0]," : ",[1])',[$post_m->getElement('department'),$post_m->getElement('name')]));
			$post_m->title_field = 'post_with_department';

			$form = $page->add('Form',null,null,['form/empty']);
			// $form->setLayout('form/aclpost');
			$form->add('xepan\base\Controller_FLC')
				->layout([
					'post'=>'c1~4',
					'type'=>'c2~6',
					'FormButtons~'=>'c3~2',
				]);

			$string_count = strpos($this->model_class, '\Model_');
			$model_namespace = substr($this->model_class,0,$string_count);
			$str = ($this->model->acl_type?$this->model->acl_type:$this->model['type']).'['.$this->model_ns.']';

			$array_list[$str] = $str;

			
			$form->addField('DropDown','post')->addClass('form-control')->setModel($post_m);
			$form->addField('DropDown','type')
										->addClass('form-control')
										// ->setModel($acl_m);
										->setValueList($array_list);
										;
			$form->addSubmit('Go')->addClass('btn btn-success');



			$af = $page->add('Form');
			if($dt){
				$is_config= false;
				try{
					$m = $this->add($ns.'\\Model_'.$dt);
				}catch(\Exception $e){
					try{
						$m = $this->add($this->entity_list[$dt]['model']);						
					}catch(\Exception $e1){
						try{
							$m = $this->add($ns.'\\'.$dt);
						}catch(\Exception $e1){
							$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
							$is_config = true;
						}
					}
					
				}

				$existing_acl = $this->add('xepan\hr\Model_ACL')
									->addCondition('post_id',$post)
									->addCondition('namespace',$ns)
									->addCondition('type',$dt)
									->tryLoadAny();
				if(!$existing_acl->loaded())
					$existing_acl->save();
			
				if(!$is_config && !$this->skip_allow_add){
					$af->addField('Checkbox','allow_add');
					$af->getElement('allow_add')->set($existing_acl['allow_add']);
				}

				if(!$is_config && !$this->skip_branch && $this->model->hasElement('branch_id')){
					$af->addField('Checkbox','is_branch_restricted');
					$af->getElement('is_branch_restricted')->set($existing_acl['is_branch_restricted']);
				}

				

				$value_list=['Self Only'=>'Self Only','All'=>'All','None'=>'None'];
				
				if(isset($m->assigable_by_field) && $m->assigable_by_field !=false)
					$value_list['Assigned To']='Assigned To Employee';

				if($is_config){
					unset($value_list['Self Only']);
				}

				foreach ($m->actions as $status => $actions) {
					$status = $status=='*'?'All':$status;	
					$greeting_card = $af->add('View', null, null, ['view/acllist1']);
					foreach ($actions as $action) {
						$greeting_card->template->set('action',$status);					
						// $greeting_card->addField('DropDown',$status.'_'.$action,$action)
						$tf = $greeting_card->addField('xepan\base\RadioButton',$status.'_'.$action,$action)
							->setValueList($value_list)
							// ->setEmptyText('Please select ACL')
							->validate('required?Acl must be provided or will work based on global permisible setting')
							->addClass('form-control xepan-custom-radio-btn-field')
							->set($existing_acl['action_allowed'][$status][$action]);
						;

						$tf->template->set('row_class','xepan-radio-btn-form-field');
					}
				}

				$af->addSubmit('Update')->addClass('btn btn-success xepan-margin-top-small');
				$af->template->set('buttons_class','xepan-radio-btn-form-field-clear');
			}

			$af->onSubmit(function($f)use($post,$ns,$dt){
				$is_config = false;
				try{
					$m = $this->add($ns.'\\Model_'.$dt);
				}catch(\Exception $e){
					try{
						$m = $this->add($this->entity_list[$dt]['model']);	
					}catch(\Exception $e){
						try{
							$m = $this->add($ns.'\\'.$dt);
						}catch(\Exception $e1){
							$m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>[1],'config_key'=>$dt]);
							$is_config = true;
						}
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

				$acl_m->addCondition('type',$m->acl_type?:$m['type']);
				$acl_m->addCondition('post_id',$post)
						;
				$acl_m->tryLoadAny();

				$acl_m['action_allowed'] = json_encode($acl_array);
				$acl_m['allow_add'] = $f['allow_add']?:false;
				$acl_m['is_branch_restricted'] = $f['is_branch_restricted']?:false;
				$acl_m->save();
				
				return $f->js(true,$f->js()->univ()->successMessage('Updated, Please reload page'))->univ()->closeDialog();
			});

			$form->onSubmit(function($f)use($af){

				$type = explode("[", $f['type']);
				$ns	=trim($type[1],']');
				
				$acl_m = $this->add('xepan\hr\Model_ACL')
						->addCondition('type',$type[0])
						->addCondition('namespace',$ns)
						->tryLoadAny()
						;												
				return $af->js()->reload(['post_id'=>$f['post'],'namespace'=>$acl_m['namespace'],'type'=> $type[0]]);
			});
		});
	}

	function manageAclErrorVP($vp){
		$vp->set(function($page){
			if($this->app->ACLModel === "Departmental"){
				$page->add('View')->addClass('alert alert-danger')->set('ACL is defined as Department Base, Please configure ACL from HR -> ACL menu');
				return;
			}
			$str = ($this->model->acl_type?$this->model->acl_type:$this->model['type']);
			$page->add('View_Error')->set($str.' is not in export entity as key, Develoepr issue')->addClass('alert alert-danger');
			$page->add('View_Info')->set('export entity must have type (field value) of model or Class name stripped `Model_` ')->addClass('alert alert-info');
		});
	}

}
