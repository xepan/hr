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


// Model array structure to use
// public $status=['Draft','Submitted','Canceled','Converted'];
// 	public $actions=[
// 			'Draft'=>['submit'],
// 			'Submitted'=>['email','cancel','convert'],
// 			'Canceled' =>[],
// 			// 'Converted'=>[default view,add,edit,delete]
// 	];


namespace xepan\hr;

class Controller_ACL extends \AbstractController {
	
	public $acl_m = null;
	public $action_allowed=[];

	function init(){
		parent::init();
	
		$this->model = $model = $this->getModel();

		$this->canDo();

		// Put Model View Conditions 

		if($model instanceof \xepan\base\Model_Document){			
			$view_array = $this->canView();			
			$q = $this->model->dsql();

			foreach ($view_array as $status => $acl) { // acl can be true(for all, false for none and [] for employee created_by_ids)
				if($status=='*'){
					if($acl === true) break;
					if($acl === false) $acl = -1; // Shuold never be reached
					$model->addCondition('created_by_id',$acl);
					break;
				}else{
					if($acl === false) continue;
					if($acl === true){
						// No employee condition .. just check status
						$this->model->_dsql()
										->orWhere(
											$q->andExpr()
												->where($q->expr('[0]=="[1]"',[$this->model->getElement('status'),$status]))
										);
					}else{
						$this->model->_dsql()
										->orWhere(
											$q->andExpr()
												->where($q->expr('[0]=="[1]"',[$this->model->getElement('status'),$status]))
												->where('created_by_id',$acl)
										);
					}
				}
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
						}
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
					if(empty($action_btn_list))
						$g->current_row_html['action']='Action Here';
					else
						$g->current_row_html['action']= $this->add('xepan\hr\View_ActionBtn')->getHTML();
				});
				$view->setFormatter('action','action');
				$view->on('click','.actions',function($js,$data){
					return $js->successMessage("Hhi");
				});

			}elseif($view instanceof \xepan\base\View_Document){

			}

			// May be you have CRUD form here

		}
		
// 		$this->owner->grid->addColumn('template','action')->setTemplate('<div class="btn-group">
// <button type="button" class="btn btn-primary">Action</button>
// <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
// <span class="caret"></span>
// </button>
// <ul class="dropdown-menu" role="menu">
// <li><a href="#">Action</a></li>
// <li><a href="#">Another action</a></li>
// <li><a href="#">Something else here</a></li>
// <li class="divider"></li>
// <li><a href="#">Separated link</a></li>
// </ul>
// </div>');

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
		return $this->acl_m['allow_add'];
	}

	function canEdit($status=null){
		if(!$status){
			$status='*';
		}
		return $this->action_allowed[$status]['edit']; // can be true/false/ or []
	}

	function canDelete($status=null){
		if(!$status) $status='*';
		return $this->action_allowed[$status]['delete']; // can be true/false/ or []
	}

	function getActions($status=null){
		if(!$status) $status='*';
		return $this->action_allowed[$status]?:[];
	}

	function canDoAction($action,$status=null){
		if(!$status) $status='*';


	}

	/**
	 * [canDo description]
	 * @return ['submit'=>[1],'can_view'=>false/true/[1,2,3]] [description]
	 */
	function canDo(){
		$class = new \ReflectionClass($this->model);
		$this->acl_m = $this->add('xepan\hr\Model_ACL')
					->addCondition('namespace',$class->getNamespaceName())
					->addCondition('document_type',$this->model['type'])
					->addCondition('post_id',$this->app->employee['post_id'])
					;
		$this->acl_m->tryLoadAny();
		if(!$this->acl_m->loaded()) $this->acl_m->save();
		
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
		foreach ($this->acl_m as $acl) {
			foreach ($acl['action_allowed'] as $status => $actions) {
				foreach ($actions as $action=>$acl_value) {
					$this->action_allowed[$status][$action] = $this->textToCode($acl_value);
				}
			}
		}

		return $this->action_allowed;
	}

	function textToCode($text){
		if($text=='' || $text == 'None') return false;
		if($text == 'All' || $text === true ) return true;
		if($text == 'Self Only') return [$this->app->employee->id];
	}
}
