<?php

namespace xepan\hr;

/**
* 
*/
class Model_File extends \xepan\base\Model_Table
{
	public $table='file';
	// public $table='file';
	public $file_type = ['SpreadSheet'=>'Spread Sheet','Word'=>'Word','PPT'=>'PPT','ToDo'=>'To Do','Upload'=>'Upload','directory'=>'directory'];
	public $status=['All'];
	public $actions=[
		'All'=>['share','edit','delete']
	];

	public $acl=false;

	public $user_id=null;
	public $department_id=null;

	function init()
	{
		parent::init();

		if(!$this->user_id) $this->user_id = $this->app->employee->id;
		if(!$this->department_id) $this->department_id = $this->app->employee['department_id'];


		$this->hasOne('xepan\hr\ParentFile','parent_id');

		$this->add('xepan\filestore\Field_Image','file_id');
		$this->hasOne('xepan\base\Contact','created_by_id')->defaultValue($this->user_id);
		$this->addField('name')->sortable(true);
		$this->addField('mime')->setValueList($this->file_type);
		
		// Needed ??? or just create new file with filestore and use that as file content
		$this->addField('content')->type('text');
		
		$this->addField('width');
		$this->addField('height');
		$this->addField('size');	

		$this->hasMany('xepan\hr\ChildFile','parent_id',null,'Children');
		$this->hasMany('xepan\hr\DocumentShare','file_id',null,'Share');

		// $this->addExpression('size')->set(function($m,$q){
		// 	$file_model = $m->add('xepan\filestore\Model_File')->tryLoad('id',$m->getElement('id'));
		// 	return $q->expr('IFNULL([0],0)',[$m->refSQL('file_id')->fieldQuery('filesize')]);
		// });
		// $this->addExpression('width')->set('"100"');
		// $this->addExpression('height')->set('"100"');

		// shared with me or my file
		$this->addExpression('read')->set(function($m,$q){
			return '"1"';
		});

		$this->addExpression('shared_with_me')->set(function($m,$q){
			// ! created_by_me && shared_with_me
			return $share = $m->refSQL('Share')
					->addCondition([
									['shared_to_id',$this->user_id],
									['shared_type','Global'],
									['department_id',$this->department_id]
								])->count()
					;
		});
		// shared permission to write or my file
		$this->addExpression('write')->set('"1"');
		$this->addExpression('locked')->set('"0"');
		$this->addExpression('hidden')->set('"0"');
		

		$this->addExpression('child_directory_count')->set(function($m,$q){
			$file = $m->add('xepan\hr\Model_File',['table_alias'=>'cdc'])->addCondition('mime','directory')->addCondition('parent_id',$m->getElement('id'));
			return $q->expr('IFNULL([0],0)',[$file->count()]);
		})->type('int');

		$this->addExpression('child_file_count')->set(function($m,$q){
			$file = $m->add('xepan\hr\Model_File',['table_alias'=>'cfc'])->addCondition('mime','<>','directory')->addCondition('parent_id',$m->getElement('id'));
			return $q->expr('IFNULL([0],0)',[$file->count()]);
		})->type('int');

		$this->addExpression('dirs')->set(function($m,$q){
			return $q->expr('IF([mime]="directory" AND [dir_count] > 0,1,0)',['mime'=>$m->getElement('mime'),'dir_count'=>$m->getElement('child_directory_count')]);
		});


		// $this->addHook('afterInsert',[$this,'personalShare']);
		// $this->addHook('beforeDelete',$this);

		$this->is([
			'name|to_trim|required'
			]);
	}

	function beforeDelete(){
		$doc_shares = $this->add('xepan\hr\Model_DocumentShare')->addCondition('file_id',$this->id);
		foreach ($doc_shares as $doc_share) {
			$doc_share->delete();
		}
	}

	function personalShare($obj,$new_id){
		throw new \Exception("why calling", 1);
		
		$share_model = $this->add('xepan\hr\Model_DocumentShare');
		$share_model['file_id'] = $new_id;
		$share_model['shared_by_id'] = $this->app->employee->id;
		$share_model['shared_to_id'] = $this->app->employee->id;
		$share_model['shared_type'] = "Personal";
		$share_model['can_edit'] = true;
		$share_model['can_delete'] = true;
		$share_model['can_share'] = true;
		$share_model->save();
	}

	function page_share($page){
		
		$share_model = $page->add('xepan\hr\Model_DocumentShare');
		$share_model->addCondition('file_id',$this->id);
		$share_model->addCondition('shared_by_id',$this->app->employee->id);

		$crud = $page->add('xepan\hr\CRUD',['pass_acl'=>true]);
		$crud->setModel($share_model,['shared_by_id','shared_to_id','department_id','shared_type','can_edit','can_delete','can_share']);
	}

	function page_edit($page){

		$form = $page->add('Form');
		$form->addField('line','name')->set($this['name'])->validate('required');
		$form->addSubmit('Update');
		if($form->isSubmitted()){
			$this['name'] = $form['name'];
			$this->save();
			//Todo Activity
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("File Renamed Successfully");
		}
	}

	function page_delete($page){
		$file_name = $this['name'];
		$form = $page->add('Form');
		$form->add('View')->setElement('h3')->set('are you sure you want to delete');
		$form->addSubmit('yes');
		if($form->isSubmitted()){
			$this->delete();
			return $form->js(null,$form->js()->univ()->closeDialog())->univ()->successMessage("file ".$file_name." deleted successfully");
		}
	}

	function haveChildDirectories(){
		return $this->add('xepan\hr\Model_File')->addCondition('parent_id',$this->id)->addCondition('mime','directory')->count()->getOne();
	}

	function iCanEdit(){
		if(!$this->loaded())
			throw new \Exception("file model must loaded", 1);

		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;

		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;
		
		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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
			throw new \Exception("file model must loaded", 1);
					
		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;
		
		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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
						->addCondition('file_id',$this->id);
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

	function iCanView(){
		if(!$this->loaded())
			throw new \Exception("file model must loaded", 1);
					
		// condition 1 if created by me 
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_type','Personal');
		$doc_share->addCondition('shared_by_id',$this->app->employee->id);
		if($doc_share->count()->getOne())
			return true;
		
		// condition 2 if shared with me (person)
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_to_id',$this->app->employee->id);
		$doc_share->addCondition('shared_type','Person');	
		if($doc_share->count()->getOne())
			return true;


		// condition 3 if department wise shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('department_id',$this->app->employee['department_id']);
		$doc_share->addCondition('shared_type','Department');
		if($doc_share->count()->getOne())
			return true;

		// condition 4 if global shared with me
		$doc_share = $this->add('xepan\hr\Model_DocumentShare')
						->addCondition('file_id',$this->id);
		$doc_share->addCondition('shared_type','Global');
		if($doc_share->count()->getOne())
			return true;

		return false;
	}

	function renderEdit($page){
		$page->add('View_Error')->setElement('h1')->set('Add function in child class of file type '.$_GET['type']);
	}

	function renderView($page){
		$page->add('View_Error')->setElement('h1')->set('Add function in child class of file type '.$_GET['type']);
	}

}