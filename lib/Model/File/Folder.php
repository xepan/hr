<?php

namespace xepan\hr;

/**
* 
*/
class Model_File_Folder extends \xepan\hr\Model_File
{
	// public $table='folder';
	public $status=['All'];
	
	public $actions=[
		'All'=>['share','edit','delete','add_new_folder','add_new_file']
	];

	function init()
	{
		parent::init();
		
		$this->addCondition('mime','directory');

		$this->hasMany('xepan\hr\File','parent_id');
		$this->hasMany('xepan\hr\DocumentShare','folder_id');
		
		$this->is([
				'name|to_trim|required'
				]);

		$this->addHook('afterInsert',[$this,'personalShare']);
		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		if($this['parent_folder_id'])
			$this->iCanManageFolder($this['parent_folder_id']);
	}

	function iCanManageFolder($folder_id){
		$folder = $this->add('xepan\hr\Model_Folder')->load($folder_id);
		if($folder->iCanEdit())
			return true;
		else
			throw new \Exception("you are not permitted to manage this folder");
	}

	function beforeDelete(){
	
		$sub_folder = $this->add('xepan\hr\Model_Folder')->addCondition('parent_folder_id',$this->id);
		foreach ($sub_folder as $folder) {
			$folder->delete();
		}
		
		$files = $this->add('xepan\hr\Model_File')->addCondition('folder_id',$this->id);
		foreach ($files as $file) {
			$file->delete();
		}

		$doc_shares = $this->add('xepan\hr\Model_DocumentShare')->addCondition('folder_id',$this->id);
		foreach ($doc_shares as $doc_share) {
			$doc_share->delete();
		}
	}

	function personalShare($obj,$new_id){
		
		$share_model = $this->add('xepan\hr\Model_DocumentShare');
		$share_model['folder_id'] = $new_id;
		$share_model['shared_by_id'] = $this->app->employee->id;
		$share_model['shared_to_id'] = $this->app->employee->id;
		$share_model['shared_type'] = "Personal";
		$share_model['can_edit'] = true;
		$share_model['can_delete'] = true;
		$share_model['can_share'] = true;
		$share_model->save();
	}

	function createNew($name,$folder_id=null){
		if(!trim($name))
			throw new \Exception("folder name must not be empty");

		$new_folder = $this->add('xepan\hr\Model_Folder');
		$new_folder['name'] = $name;
		$new_folder['parent_folder_id'] = $folder_id;
		$new_folder->save();
		return $new_folder;
	}

	function page_share($page){
		
		$share_model = $page->add('xepan\hr\Model_DocumentShare');
		$share_model->addCondition('folder_id',$this->id);
		$share_model->addCondition('shared_by_id',$this->app->employee->id);

		$crud = $page->add('xepan\hr\CRUD');
		$crud->setModel($share_model,['shared_by_id','shared_to_id','department_id','shared_type','can_edit','can_delete','can_share']);
	}

	function page_add_new_folder($page){
		$form = $page->add('Form');
		$form->addField('line','folder_name')->validate('required');
		$form->addSubmit('Add Folder');
		if($form->isSubmitted()){
			$new_folder = $this->add('xepan\hr\Model_Folder');
			$new_folder['parent_folder_id'] = $this->id;
			$new_folder['name'] = $form['folder_name'];
			$new_folder->save();
			
			//Todo Activity
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("New Folder '".$form['folder_name']."' Added");
		}
	}

	function page_add_new_file($page){
		$file_model = $page->add('xepan\hr\Model_File');

		$form = $page->add('Form');
		$form->addField('line','file_name')->validate('required');
		$form->addField('DropDown','file_type')->validate('required')->setValueList($file_model->file_type);
		$form->addSubmit('Add File');
		if($form->isSubmitted()){
			$file_model = $this->add('xepan\hr\Model_File_'.$form['file_type']);
			$file_model['folder_id'] = $this->id;
			$file_model['name'] = $form['file_name'];
			$file_model->save();
			//Todo Activity
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("New file '".$form['file_name']."' Added");
		}
	}

	function page_edit($page){

		$form = $page->add('Form');
		$form->addField('line','name')->set($this['name'])->validate('required');
		$form->addSubmit('Update');
		if($form->isSubmitted()){
			$this['name'] = $form['name'];
			$this->save();
			
			//Todo Activity
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("Folder Name Update Successfully");
		}
	}

	function page_delete($page){
		$folder_name = $this['name'];

		$form = $page->add('Form');
		$form->add('View')->setElement('h3')->set('are you sure you want to delete');
		$form->add('View')->set('Total Number of Sub Folder: '.$page->add('xepan\hr\Model_Folder')->addCondition('parent_folder_id',$this->id)->count()->getOne());
		$form->add('View')->set('Total Number of File: '.$page->add('xepan\hr\Model_File')->addCondition('folder_id',$this->id)->count()->getOne());
		$form->addSubmit('yes');
		if($form->isSubmitted()){
			$this->delete();
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("Folder ".$folder_name." deleted successfully");
		}
	}

	function iCanEdit(){
		if(!$this->loaded())
			throw new \Exception("folder model must loaded", 1);

		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;

		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_to_id',$this->app->employee->id);
		$doc_share->addCondition('shared_type','Person');
		$doc_share->tryLoadAny();
		
		if($doc_share->loaded()){
			if($doc_share['can_edit'])
				return true;
			else
				return false;
		}


		// condition 3 if department wise shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('department_id',$this->app->employee['department_id']);
		$doc_share->addCondition('shared_type','Department');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_edit'])
				return true;
			else
				return false;
		}

		// condition 4 if global shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Global');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_edit'])
				return true;
			else
				return false;
		}

		return false;
	}

	function iCanDelete(){
		if(!$this->loaded())
			throw new \Exception("folder model must loaded", 1);
					
		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;
		
		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_to_id',$this->app->employee->id);
		$doc_share->addCondition('shared_type','Person');
		$doc_share->tryLoadAny();
		
		if($doc_share->loaded()){
			if($doc_share['can_delete'])
				return true;
			else
				return false;
		}


		// condition 3 if department wise shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('department_id',$this->app->employee['department_id']);
		$doc_share->addCondition('shared_type','Department');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_delete'])
				return true;
			else
				return false;
		}

		// condition 4 if global shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Global');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_delete'])
				return true;
			else
				return false;
		}

		return false;
	}

	function iCanShare(){
		if(!$this->loaded())
			throw new \Exception("folder model must loaded", 1);
					
		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;
		
		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_to_id',$this->app->employee->id);
		$doc_share->addCondition('shared_type','Person');
		$doc_share->tryLoadAny();
		
		if($doc_share->loaded()){
			if($doc_share['can_share'])
				return true;
			else
				return false;
		}


		// condition 3 if department wise shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('department_id',$this->app->employee['department_id']);
		$doc_share->addCondition('shared_type','Department');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_share'])
				return true;
			else
				return false;
		}

		// condition 4 if global shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('folder_id',$this->id);
		$doc_share->addCondition('shared_type','Global');
		$doc_share->tryLoadAny();

		if($doc_share->loaded()){
			if($doc_share['can_share'])
				return true;
			else
				return false;
		}

		return false;
	}
}