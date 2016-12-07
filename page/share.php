<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr {

	class page_share extends \Page {
		public $title='Share';

		function init(){
			parent::init();

		$file_id = $this->app->stickyGET('file_id');

		if(!$file_id or $file_id == "undefined"){
			$this->add('View_Error')->setElement('h2')->set('file not found, reload the page try agian '.$file_id);
			return;
		}

		$share_model = $this->add('xepan\hr\Model_DocumentShare');
		$share_model->addCondition('file_id',$file_id);

		$crud = $this->add('xepan\hr\CRUD',['pass_acl'=>true]);
		
		if($crud->isEditing()){

		}
		$crud->setModel($share_model,['shared_type','shared_to_id','department_id','department','can_edit','can_delete','can_share'],['shared_type','shared_to','department','can_edit','can_delete','can_share']);
		
		if($crud->isEditing()){	

			$form = $crud->form;
			$type_field = $form->getElement('shared_type');
			// $type_field->getElement('file_id');
			$type_field->js(true)->univ()->bindConditionalShow([
					'Global'=>['can_edit','can_delete','can_share'],
					'Department'=>['department_id','can_edit','can_delete','can_share'],
					'Person'=>['shared_to_id','can_edit','can_delete','can_share']
				],'div.atk-form-row');
		}
		


		}
	}
}
