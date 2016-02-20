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

class page_department extends \Page {
	public $title='Department';

	function init(){
		parent::init();

		
		$department=$this->add('xepan\hr\Model_Department');

		// $userlist = $this->add('CompleteLister',null,null,['page\department']);
		// $userlist->setModel($department);

		// $userlist->on('click','.post-link',$this->js()->univ()->location([$this->api->url('xepan_hr_contact_post'),'id'=>$this->js()->_selectorThis()->closest('tr')->data('id')]));

		$crud=$this->add('xepan\base\CRUD',
						array(
							'grid_class'=>'xepan\base\Grid',
							'grid_options'=>array(
											'defaultTemplate'=>['grid/department-grid']
											)
						));

		$crud->setModel($department);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
