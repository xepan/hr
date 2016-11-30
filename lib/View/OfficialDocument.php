<?php

namespace xepan\hr;

class View_OfficialDocument extends \xepan\base\Grid{
	public $officialdocument_type = "Folder";
	function init(){
		parent::init();
	
		$this->view_reload_url = $this->app->url(null,['cut_object'=>$this->name]); 
	}
	
	function formatRow(){
		$thisTask = $this->model;

		$action_btn_list = $this->model->actions[$this->model['status']?:"All"];

		if($this->officialdocument_type == "Folder"){
			$edit_action = ['edit','add_new_folder','add_new_file'];
			$action_name = "<a href='".$this->api->url(null,['folder_id'=>$this->model->id])."'>".$this->model['name']."</a>";
		}else{
			$edit_action = ['edit'];
			$action_name = "<a target='_blank' href='".$this->api->url('xepan_hr_file',['id'=>$this->model->id,'type'=>$this->model['type']])."'>".$this->model['name']."</a>";
		}
		
		// manage action list
		if(!$this->model->iCanEdit()){
			$action_btn_list = array_diff($action_btn_list, $edit_action);
		}

		if(!$this->model->iCanDelete()){
			$edit_action = ['delete'];
			$action_btn_list = array_diff( $action_btn_list, $edit_action);
		}

		if(!$this->model->iCanShare()){
			$edit_action = ['share'];
			$action_btn_list = array_diff( $action_btn_list, $edit_action);
		}

		$action_btn = $this->add('AbstractController')->add('xepan\hr\View_ActionBtn',['actions'=>$action_btn_list?:[],'id'=>$this->model->id,'status'=>$action_name]);
		$this->current_row_html['action'] = $action_btn->getHTML();
		
		// $this->current_row_html['starting_date'] = date_format(date_create($this['starting_date']), 'g:ia jS F Y');
		return parent::formatRow();
	}

	function setModel($model,$fields=null){
		$m= parent::setModel($model,$fields);		
		$this->on('click','.acl-action',[$this,'manageAction']);
		
		return $m;
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
					$page_action_result = $this->model->$action();
					$this->api->db->commit();
				}catch(\Exception_StopInit $e){

				}catch(\Exception $e){					
					$this->api->db->rollback();
					throw $e;
				}

				$js=[];
				if(isset($page_action_result) or isset($this->app->page_action_result)){
					
					if(isset($this->app->page_action_result)){						
						$page_action_result = $this->app->page_action_result;
					}

					if($page_action_result instanceof \jQuery_Chain) {
						$js[] = $page_action_result;
					}
					$this->getView()->js(null,$js)->reload(null,null,$this->view_reload_url)->execute();
				}
				$this->getView()->js()->reload(null,null,$this->view_reload_url)->execute();
			// $this->getView()->js()->univ()->location()->execute();
		}else{
			return $js->univ()->errorMessage('Action "'.$action.'" not defined in Model');
		}
	}

	function getView(){
		return $this;
	}

	function defaultTemplate(){
		if($this->officialdocument_type == "File"){
			return['view/officialdocument1','file_lister'];
		}else
			return['view/officialdocument1','folder_lister'];
	}
}