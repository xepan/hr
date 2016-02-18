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

		

		$form = $this->add('Form');
		$form->setLayout(['page/post']);
		$form->setModel($this->api->auth->model->reload(),['department_name']);

		$form->onSubmit(function($f){
			// return $f->displayError('first_name','HELLO');
			$f->save();
			return $f->js()->reload();
		});
		
	}
}
