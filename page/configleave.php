<?php
namespace xepan\hr;

class page_configleave extends \xepan\hr\page_configurationsidebar{
	public $title = "Leave Template";

	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$leave_tab = $tabs->addTab('Leave');

		$leave = $this->add('xepan\hr\Model_Leave');
		$crud = $leave_tab->add('xepan\hr\CRUD');
		// $crud->form->add('xepan\base\Controller_FLC')
		// 	->layout([
		// 		'name'=>'c1~3',
				// 'type'=>'c2~3',
				// 'no_of_leave'=>'c3~3',
				// 'unit'=>'c4~3',
				// 'is_yearly_carried_forward~'=>'c5~3',
				// 'is_unit_carried_forward~'=>'c6~3',
				// 'allow_over_quota~'=>'c7~3'
			// ]);

		$crud->setModel($leave,['name']);
		// $crud->setModel($leave,['name','is_yearly_carried_forward','type','is_unit_carried_forward','no_of_leave','unit','allow_over_quota']);
		$crud->grid->addPaginator(50);
		$crud->grid->removeAttachment();
		$crud->grid->addSno();
		$crud->grid->addQuickSearch(['name']);

		$tabs->addTabURL('xepan_hr_leavetemplate','Leave Templates');
	}
}