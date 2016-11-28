<?php

namespace xepan\hr;

/**
* 
*/
class Model_File extends \xepan\hr\Model_Document
{
	// public $table='file';
	
	function init()
	{
		parent::init();

		$file_j = $this->join('file.document_id');
		$file_j->hasOne('xepan\hr\Folder','folder_id');
		$file_j->addField('name')->sortable(true);
		$file_j->addField('content')->type('text');
		// $file_j->addField('content_type')->setValueList(['spreadsheet'=>'Spread Sheet','word'=>'Word','ppt'=>'PPT','todo'=>'To Do']);
		
		$file_j->hasMany('xepan\hr\DocumentShare','file_id');

		$this->is([
			'name|to_trim|required'
			]);
	}
}