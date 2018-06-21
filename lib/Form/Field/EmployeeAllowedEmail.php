<?php

namespace xepan\hr;


class Form_Field_EmployeeAllowedEmail extends \xepan\base\Form_Field_DropDown {

	public $for_post = null;
	public $validate_values = false;

	function init(){
		parent::init();
		$this->setEmptyText('Please Select');
	}

	function forPost($post){
		$this->for_post= $post;
	}

	function recursiveRender(){
		// if(!$this->for_post) $this->for_post = $this->app->employee->post();
		// $email_settings = $this->for_post->associatedEmailSettings();
		// $this->setModel($email_settings);
		$this->setModel('xepan\hr\Post_Email_MyEmails');
		parent::recursiveRender();
	}
}