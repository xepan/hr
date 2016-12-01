<?php

namespace xepan\hr;

/**
* 
*/
class Model_File_Word extends \xepan\hr\Model_File
{
	
	function init()
	{
		parent::init();
		
		$this->addCondition('type','word');

	}

	function createNew($name,$folder_id=null){
		if(!trim($name))
			throw new \Exception("folder name must not be empty");

		$new_file = $this->add('xepan\hr\Model_File_Word');
		$new_file['name'] = $name;
		$new_file['folder_id'] = $folder_id;
		$new_file->save();
		return $new_file;
	}

	function renderEdit($page){
		if(!$this->loaded())
			throw new \Exception("model word must loaded", 1);
		
		$this->getElement('content')->display(['form'=>'xepan\base\RichText']);
		$form = $page->add('Form');
		$form->setModel($this,['name','content']);
		$form->addSubmit('Save');
		if($form->isSubmitted()){
			$form->save();
			$form->js()->univ()->successMessage('saved')->execute();
		}
	}

	function renderView($page){
		if(!$this->loaded())
			throw new \Exception("model word must loaded", 1);
		$page->add('View')->setElement('h2')->set($this['name']);
		$page->add('View')->setHtml($this['content']);
	}
}