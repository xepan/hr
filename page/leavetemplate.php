<?php

namespace xepan\hr;

class page_leavetemplate extends \xepan\base\Page {
	
	function page_index(){

		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$temp_crud = $this->add('xepan\hr\CRUD');
		$temp_crud->setModel($leave_template_m,['name']);

		$temp_crud->grid->addColumn('expanderPlus','Detail');
		$temp_crud->grid->addPaginator(25);
		$temp_crud->grid->addQuickSearch(['name']);
		$temp_crud->grid->addSno();
		// $temp_crud->grid->removeColumn('action');
		$temp_crud->grid->removeAttachment();
	}

	function page_Detail(){
		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$leave_template_m->load($this->app->stickyGET('leave_template_id'));

		$leave_template_detail = $leave_template_m->ref('xepan\hr\LeaveTemplateDetail');

		$crud=$this->add('xepan\hr\CRUD');
		$crud->form->add('xepan\base\Controller_FLC')
			->addContentSpot()
			->layout([
				'leave_id~leave'=>'c1~3',
				'type'=>'c2~3',
				'no_of_leave'=>'c3~3',
				'unit'=>'c4~3',
				'is_yearly_carried_forward~'=>'c5~3',
				'is_unit_carried_forward~'=>'c6~3',
				'allow_over_quota~'=>'c7~3'
			]);

		$crud->setModel($leave_template_detail,
				['leave_id','is_yearly_carried_forward','type','is_unit_carried_forward','unit','allow_over_quota','no_of_leave'],
				['leave','is_yearly_carried_forward','type','is_unit_carried_forward','unit','allow_over_quota','no_of_leave']
				);

		// $crud->grid->removeColumn('action');
		$crud->grid->removeAttachment();
		$crud->grid->addPaginator(25);
		$crud->grid->addQuickSearch(['leave']);
		$crud->grid->addSno();
	}

}