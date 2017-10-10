<?php

/**
* description: Document is a global model for almost all documents in xEpan platform.
* Main purpose of document model/table is to give a system wide unique id for all documents spreaded 
* in various tables.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Model_Document extends \xepan\base\Model_Document{

	function init(){
		parent::init();
		
		$this->getElement('created_by_id')->defaultValue(@$this->app->employee->id)->display(['form'=>'xepan\hr\Employee']);
		$this->getElement('updated_by_id')->defaultValue(@$this->app->employee->id);
	}

	function page_manage_attachments($p){
		// $crud = $p->add('xepan\base\CRUD');
		// $crud->setModel($this->ref('Attachments'));

		$crud = $p->add('xepan\hr\CRUD',null,null,['view\documentattachment']);
		$crud->setModel($this->ref('Attachments'));
	}
}
