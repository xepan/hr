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
		$department->tryLoadAny();
		$form = $this->add('Form');
		$form->setLayout(['page/department']);
		$form->setModel($department,['name','production_level','status','posts']);
		$this->add('CRUD')->setModel($department);
		$form->onSubmit(function($f){
			// return $f->displayError('first_name','HELLO');
			$f->save();
			return $f->js()->reload();
		});
		
	}
}
