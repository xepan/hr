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

		

		$form = $this->add('Form');
		$form->setLayout(['view/xhr/department']);
		$form->setModel($this->api->auth->model->reload(),['department_name']);

		$form->onSubmit(function($f){
			// return $f->displayError('first_name','HELLO');
			$f->save();
			return $f->js()->reload();
		});
		
	}
}
