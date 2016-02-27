<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_post extends \Page {
	public $title='Post';

	function init(){
		parent::init();

		$this->api->stickyGET('department_id');

		$post=$this->add('xepan\hr\Model_Post');
		if($_GET['department_id']){
			$post->addCondition('department_id',$_GET['department_id']);
		}

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/post/post-grid']);

		$crud->setModel($post);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
