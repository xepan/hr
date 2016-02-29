<?php
namespace xepan\hr;
class page_employee_setting extends \Page{
	public $title="Settings";
	function init(){
		parent::init();

		$this->add('H1')->set('Employee Settings');


	}
}