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
		
		$share_model = $this->add('xepan\hr\Model_DocumentShare');
		$share_model->addCondition('file_id',$file_id);
		$share_model->addCondition('shared_by_id',$this->app->employee->id);

		$crud = $this->add('xepan\hr\CRUD',['pass_acl'=>true]);
		$crud->setModel($share_model,['shared_by_id','shared_to_id','department_id','shared_type','can_edit','can_delete','can_share']);
		
		}
	}
}
