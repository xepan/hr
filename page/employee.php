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

class page_employee extends \Page {
	public $title='Employee';

	function init(){
		parent::init();

		

		$form = $this->add('Form');
		$form->setLayout(['page/employee']);
		$form->setModel($this->api->auth->model->reload(),['department_name']);

		$form->onSubmit(function($f){
			// return $f->displayError('first_name','HELLO');
			$f->save();
			return $f->js()->reload();
		});
		
	}
}
