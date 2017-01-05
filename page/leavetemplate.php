<?php

namespace xepan\hr;

class page_leavetemplate extends \xepan\base\Page {
	
	function page_index(){

		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$temp_crud = $this->add('xepan\hr\CRUD',null,null,['page/config/empleavetemplate']);
		$temp_crud->setModel($leave_template_m);

		$temp_crud->grid->addColumn('expander','Detail');
		$temp_crud->grid->addPaginator(5);
		$temp_crud->grid->addQuickSearch(['name']);
	}

	function page_Detail(){
		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$leave_template_m->load($this->app->stickyGET('leave_template_id'));

		$leave_template_detail=$leave_template_m->ref('xepan\hr\LeaveTemplateDetail');

		$crud=$this->add('xepan\hr\CRUD',null,null,['page/config/empleavetemplatedetail']);
		$crud->setModel($leave_template_detail);
		// $crud->grid->addQuickSearch(['leave']);
	}

}