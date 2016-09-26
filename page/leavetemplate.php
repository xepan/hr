<?php
namespace xepan\hr;

class page_leavetemplate extends \xepan\hr\page_config{
	public $title = "Leave Template";

	function init(){
		parent::init();
		
		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$temp_crud = $this->add('xepan\base\CRUD',null,'leave_template_view');
		$temp_crud->setModel($leave_template_m);

		$temp_crud->addRef('xepan\hr\LeaveTemplateDetail',['label'=>'Detail']);


		$leave = $this->add('xepan\hr\Model_Leave');
		$crud = $this->add('xepan\base\CRUD',null,'leave_view');
		$crud->setModel($leave);


	}

	function defaultTemplate(){
		return ['page/leavetemplate'];
	}
}