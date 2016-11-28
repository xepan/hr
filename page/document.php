<?php

namespace xepan\hr;

class page_document extends \xepan\base\Page{
	public $title="Document Management";

	function init(){
		parent::init();

		$this->folder_id = $this->api->stickyGET('folder_id');

		// Action Button to do move into separat  view so manage according to acl permission
		$this->action = $this->add('View',null,'action',['page\document','action']);
		
		//adding modal popup 
		$this->modal_popup = $modal_popup = $this->add('xepan\base\View_ModelPopup',null,'modal_popup')->addClass('xepan-document-add-new-popup');
		
		// adding form 
		$this->form = $form = $modal_popup->add('Form');
		$form->addField('line','name')->validate('required');
		$this->field_folder = $form->addField('Hidden','folder_id')->set($this->folder_id);
		$this->field_new_type = $form->addField('Hidden','add_new_document_type');

		// lister of folder
		$this->lister_folder = $this->add('xepan\hr\Grid',null,'folder_lister',['page\document','folder_lister']);

		// file lister
		$this->lister_file = $this->add('xepan\hr\Grid',null,'file_lister',['page\document','file_lister']);
	}

	function recursiveRender(){
		// on click on new button open modal popup
			$js = [
				$this->js()->_selector('#'.$this->modal_popup->name)->modal(),
				$this->js()->_selector("#".$this->modal_popup->name." .modal-header h4")->text($this->js()->_selectorThis()->data('title')),
				$this->js()->_selector("#".$this->modal_popup->name." #".$this->field_new_type->name)->val($this->js()->_selectorThis()->data('documenttype'))
			];
		$this->action->js('click',$js)->_selector('li');

		// form submition handle
		if($this->form->isSubmitted()){

			try{
				$document_model = $this->add('xepan\hr\Model_'.$this->form['add_new_document_type']);
				$document_model->createNew($this->form['name'],$this->form['folder_id']);
			}catch(Exception $e){
				$this->form->js()->univ()->errorMessage('error occured '.$e->message())->execute();
			}
			$js = [$this->js()->_selector('#'.$this->modal_popup->name)->modal('toggle')];
			$this->form->js(null,$js)->univ()->successMessage("Added Successfully")->execute();
		}

		// lister folder 
		$model_folder = $this->add('xepan\hr\Model_Folder');
		if($this->folder_id)
			$model_folder->addCondition('parent_folder_id',$this->folder_id);
		$this->lister_folder->setModel($model_folder);


		// lister file
		$model_file = $this->add('\xepan\hr\Model_File');
		if($this->folder_id)
			$model_file->addCondition('folder_id',$this->folder_id);
		$this->lister_file->setModel($model_file);


		parent::recursiveRender();
	}

	function defaultTemplate(){
		return ['page\document'];
	}
}