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

class Controller_Action extends \AbstractController {
	
	public $acl_m = null;
	public $action_allowed=null;  // final array of employees ids in [status][action] key
	public $action_allowed_raw= []; // raw text in [status][action] like 'Self Only' or "NO" or "Assigned To"
	public $permissive_acl=false;
	public $action_btn_group=null;
	public $view_reload_url=null;
	public $dependent = false;

	public $based_on_model=null;
	public $based_on_view=null;

	public $model_class=null;
	public $model_ns = null;
	public $status_color = [];

	public $show_spot_acl=true;

	function init(){
		parent::init();
		
		if(!$this->app->epan->isApplicationInstalled('xepan\hr')) return;
		
		$this->model = $model = $this->getModel();

		$this->view_reload_url = $this->app->url(null,['cut_object'=>$this->getView()->name]);

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

					if(isset($actions['view'])) unset($actions['view']);
					
					$action_btn_list = $actions;
					if(!isset($g->current_row_html['action']))
						$g->current_row_html['action']= $this->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$g->model->id,'status'=>$g->model['status'],'action_btn_group'=>$this->action_btn_group,'status_color'=>$this->status_color])->getHTML();
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

			}elseif($view instanceof \View){
				if(!isset($view->effective_template))
					$view->effective_template = $view->template;
				if($view->effective_template->hasTag('action') && $this->model->loaded()){
					$actions = $this->getActions($this->model['status']);
					if(isset($actions['edit'])) unset($actions['edit']);
					if(isset($actions['view'])) unset($actions['view']);
					if(isset($actions['delete'])) unset($actions['delete']);
					
					$action_btn_list = $actions;
					$view->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list,'id'=>$this->model->id,'status'=>$this->model['status'],'action_btn_group'=>'xs','status_color'=>$this->status_color],'action')->getHTML();
					if(!isset($this->app->acl_action_added[$view->name])){
						if(!isset($this->app->acl_action_added[$view->name]))
							$view->effective_object=$view;
						$view->effective_object->on('click','.acl-action',[$this,'manageAction']);
						$this->app->acl_action_added[$view->name] = true;
					}
				}
			}
		}
	}

	function getModel(){
		if($this->based_on_model){
			$model=$this->add($this->based_on_model);
		}else{
			$model = $this->owner instanceof \Model ? $this->owner: $this->owner->model;
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

	function getActions($status=null){
		if(!$status) $status ='All';

		$array = $this->model->actions[$status]?:[];

		return $this->action_allowed = array_combine($array, $array);;
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

				$acl_m->addCondition('type',$m->acl_type?:$m['type']);
				$acl_m->addCondition('post_id',$post)
						;
				$acl_m->tryLoadAny();
				$acl_m['action_allowed'] = json_encode($acl_array);
				$acl_m['allow_add'] = $f['allow_add']?:false;
				$acl_m->save();
				return "Done";
			});

			$form->onSubmit(function($f)use($af){

				$type = explode("[", $f['type']);
				$ns	=trim($type[1],']');
				
				$acl_m = $this->add('xepan\hr\Model_ACL')
						->addCondition('type',$type[0])
						->addCondition('namespace',$ns)
						->tryLoadAny()
						;												
				return $af->js()->reload(['post_id'=>$f['post'],'namespace'=>$acl_m['namespace'],'type'=> $acl_m['type']]);
			});
		});
	}

	function manageAclErrorVP($vp){
		$vp->set(function($page){
			$str = ($this->model->acl_type?$this->model->acl_type:$this->model['type']);
			$page->add('View_Error')->set($str.' is not in export entity as key, Develoepr issue')->addClass('alert alert-danger');
			$page->add('View_Info')->set('export entity must have type (field value) of model or Class name stripped `Model_` ')->addClass('alert alert-info');
		});
	}

}
