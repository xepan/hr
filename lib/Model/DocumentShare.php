<?php

namespace xepan\hr;

/**
* 
*/
class Model_DocumentShare extends \xepan\base\Model_Table
{
	public $table='document_share';
	public $title_field = "shared_type";

	function init()
	{
		parent::init();

		$this->hasOne('xepan\hr\Folder','folder_id');
		$this->hasOne('xepan\hr\File','file_id');
		$this->hasOne('xepan\base\Contact','shared_by_id');
		$this->hasOne('xepan\base\Contact','shared_to_id');
		$this->hasOne('xepan\base\Department','department_id');
		
		$this->addField('shared_type')->enum('Global','Department','Person','Personal')->defaultValue('Personal');
		$this->addField('created_at')->type('date')->defaultValue($this->app->now)->sortable(true);

		$this->is([
			'shared_to_id|required',
			'shared_by_id|required',
			'shared_type|required',
			]);

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		// check validate
		// EITHER FILE OR FOLDER MUST REQUIRED
		if(!($this['file_id'] && $this['folder_id'])){
			throw $this->exception('either Folder or File must select', 'ValidityCheck')->setField('file_id');			
		}

		// TYPE IS DOCUMENT THEN DOCUMENT MUST REQUIRED
		if($this['shared_type'] === "Department" && !$this['department_id']){
			throw $this->exception('Please Select Department', 'ValidityCheck')->setField('department_id');
		}
	}
}