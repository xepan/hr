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

		$post=$this->add('xepan\hr\Model_Post');

		$crud=$this->add('xepan\base\CRUD',
						array(
							'grid_class'=>'xepan\base\Grid',
							'grid_options'=>array(
											'defaultTemplate'=>['grid/post-grid']
											)
						));

		$crud->setModel($post);
		$crud->grid->addQuickSearch(['name']);
		
	}
}
