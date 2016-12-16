<?php

namespace xepan\hr;

class page_document extends \xepan\base\Page{
	public $title="Document Management";

	function init(){
		parent::init();

		$this->folder_id = $this->api->stickyGET('folder_id');
		
		if($this->folder_id){
			$select_folder = $this->add('xepan\hr\Model_Folder')->addCondition('id',$this->folder_id)->tryLoadAny();
			if(!$select_folder->loaded()){
				$this->add('View',null,'header')->setElement('H3')->set("Folder not found");
				$this->folder_id = 0;
				return;
			}
			
			$this->add('View',null,'header')->set("Current Folder: ".$select_folder['name'])->addClass('btn btn-success');
		}

		// Action Button to do move into separat  view so manage according to acl permission
		if($this->folder_id == null OR ($this->folder_id AND isset($select_folder) AND $select_folder->iCanEdit())){
			$this->action = $this->add('View',null,'action',['page\document','action']);		
		}else{
			$this->action = $this->add('View',null,'action')->set('Not Permitted To Manage This Folder');
		}

		
		//adding modal popup 
		$this->modal_popup = $modal_popup = $this->add('xepan\base\View_ModelPopup',null,'modal_popup')->addClass('xepan-document-add-new-popup');
		
		// adding form 
		$this->form = $form = $modal_popup->add('Form');
		$form->addField('line','name')->validate('required');
		$this->field_folder = $form->addField('Hidden','folder_id')->set($this->folder_id);
		$this->field_new_type = $form->addField('Hidden','add_new_document_type');

		// lister of folder
		$lister_folder = $this->add('xepan\hr\View_OfficialDocument',null,'folder_lister');
		$model_folder = $this->add('xepan\hr\Model_Folder');

		// condition, created by me or shared with me or global or department is my department
		$share_folder_j = $model_folder->join('document_share.folder_id');
		$share_folder_j->addField('folder_id');
		$share_folder_j->addField('shared_by_id');
		$share_folder_j->addField('shared_to_id');
		$share_folder_j->addField('department_id');
		$share_folder_j->addField('shared_type');
		
		$model_folder->addCondition([
										['shared_by_id',$this->api->employee->id],
										['shared_to_id',$this->api->employee->id],
										['department_id',$this->api->employee['department_id']],
										['shared_type','Global']
									]);
		if($this->folder_id)
			$model_folder->addCondition('parent_folder_id',$this->folder_id);
		else
			$model_folder->addCondition('parent_folder_id',null);
		$model_folder->_dsql()->group($model_folder->dsql()->expr('[0]',[$model_folder->getElement('folder_id')]));
		
		$lister_folder->setModel($model_folder);

		// file lister
		$lister_file = $this->add('xepan\hr\View_OfficialDocument',['officialdocument_type'=>'File'],'file_lister');
		$model_file = $this->add('\xepan\hr\Model_File');
		$share_file_j = $model_file->join('document_share.file_id');
		$share_file_j->addField('file_id');
		$share_file_j->addField('shared_by_id');
		$share_file_j->addField('shared_to_id');
		$share_file_j->addField('department_id');
		$share_file_j->addField('shared_type');
		
		$model_file->addCondition([
										['shared_by_id',$this->api->employee->id],
										['shared_to_id',$this->api->employee->id],
										['department_id',$this->api->employee['department_id']],
										['shared_type','Global']
									]);

		if($this->folder_id)
			$model_file->addCondition('folder_id',$this->folder_id);
		else
			$model_file->addCondition('folder_id',null);
		$model_file->_dsql()->group($model_file->dsql()->expr('[0]',[$model_file->getElement('file_id')]));

		$lister_file->setModel($model_file);
	}

	function recursiveRender(){
		// if folder is wrong for model folder not loaded so return
		if( $this->folder_id === 0)
			return parent::recursiveRender();

		// on click on new button open modal popup
			$js = [
				$this->js()->_selector('#'.$this->modal_popup->name)->modal(),
				$this->js()->_selector("#".$this->modal_popup->name." .modal-header h4")->text($this->js()->_selectorThis()->data('title')),
				$this->js()->_selector("#".$this->modal_popup->name." #".$this->field_new_type->name)->val($this->js()->_selectorThis()->data('documenttype'))
			];
		$this->action->js('click',$js)->_selector('li.xepan-new-document');

		// form submition handle
		if($this->form->isSubmitted()){

			try{
				$this->api->db->beginTransaction();
				$document_model = $this->add('xepan\hr\Model_'.$this->form['add_new_document_type']);
				$document_model->createNew($this->form['name'],$this->form['folder_id']);
				$this->api->db->commit();
			}catch(Exception $e){
				$this->api->db->rollback();
				
				$this->form->js()->univ()->errorMessage('error occured '.$e->message())->execute();
			}
			$js = [$this->js()->_selector('#'.$this->modal_popup->name)->modal('toggle')];
			$this->form->js(null,$js)->univ()->successMessage("Added Successfully")->execute();
		}

		parent::recursiveRender();
	}

	function defaultTemplate(){
		return ['page\document'];
	}
}