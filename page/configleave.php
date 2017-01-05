<?php
namespace xepan\hr;

class page_configleave extends \xepan\hr\page_configurationsidebar{
	public $title = "Leave Template";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$leave_tab = $tabs->addTab('Leave');

		$leave = $this->add('xepan\hr\Model_Leave');
		$crud = $leave_tab->add('xepan\hr\CRUD',null,null,['page/config/leavedetail']);
		$crud->setModel($leave);

		$tabs->addTabURL('xepan_hr_leavetemplate','Leave Templates');
	}
}