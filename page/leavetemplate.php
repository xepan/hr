<?php
namespace xepan\hr;

class page_leavetemplate extends \xepan\hr\page_config{
	public $title = "Leave Template";

	function init(){
		parent::init();

		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$temp_crud = $this->add('xepan\base\CRUD',null,'leave_template_view');
		// $temp_crud = $this->add('xepan\base\CRUD',null,'leave_template_view',['page/config/empleavetemplate']);
		$temp_crud->setModel($leave_template_m);

		// $temp_crud->grid->addColumn('expander','Detail');
		$temp_crud->addRef('xepan\hr\LeaveTemplateDetail',['label'=>'Detail']);


		$leave = $this->add('xepan\hr\Model_Leave');
		// $crud = $this->add('xepan\base\CRUD',null,'leave_view');
		$crud = $this->add('xepan\base\CRUD',null,'leave_view',['page/config/leavedetail']);
		$crud->setModel($leave);


	}

	function page_Detail(){
		$leave_template_m = $this->add('xepan\hr\Model_LeaveTemplate');
		$leave_template_m->load($this->app->stickyGET('leave_template_id'));

		$leave_template_detail=$leave_template_m->ref('xepan\hr\LeaveTemplateDetail');

		$crud=$this->add('xepan\base\CRUD',null,null,['page/config/empleavetemplatedetail']);
		$crud->setModel($leave_template_detail);
		// $crud->grid->addQuickSearch(['leave']);
	}

	function defaultTemplate(){
		return ['page/leavetemplate'];
	}
}