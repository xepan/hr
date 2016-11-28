<?php

namespace xepan\hr;

/**
* 
*/
class Model_Folder extends \xepan\hr\Model_Document
{
	// public $table='folder';
	
	function init()
	{
		parent::init();
		
		$this->addCondition('type','Folder');

		$folder_j = $this->join('folder.document_id');
		$folder_j->hasOne('xepan\hr\ParentFolder','parent_folder_id');
		$folder_j->addField('name')->sortable(true);

		$folder_j->hasMany('xepan\hr\File','folder_id');
		$folder_j->hasMany('xepan\hr\folder','parent_folder_id',null,'SubFolder');
		$folder_j->hasMany('xepan\hr\DocumentShare','folder_id');
		
		$this->is([
				'name|to_trim|required'
				]);
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
}