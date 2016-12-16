<?php

namespace xepan\hr;

class page_file extends \xepan\base\Page{
	public $title = "File";
	
	function init(){
		parent::init();
		
		$file_type_list = ['spreadsheet'=>'SpreadSheet','word'=>'Word','ppt'=>'PPT','todo'=>'To Do','upload'=>'Upload'];
		$file_type = $this->api->stickyGET('type');
		$file_id = $this->api->stickyGET('id');

		$model = $this->add('xepan\hr\Model_File_'.$file_type_list[$_GET['type']]);
		$model->tryLoad($file_id);

		if(!$model->loaded()){
			$this->add('View')->set('file not found');
			return;
		}
		
		//check i can edit
		if($model->iCanEdit()){
			$model->renderEdit($this);
			return;
		}

		//check i can view
		if($model->iCanView()){
			$model->renderView($this);
			return;
		}

		$this->add('View_Warning')->set('you are not allowed to view this file');

	}
}