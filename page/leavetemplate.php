<?php
namespace xepan\hr;

class page_leavetemplate extends \xepan\hr\page_configurationsidebar{
	public $title = "Leave Template";

	function init(){
		parent::init();
		
		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($leave_template_m);
	}
}