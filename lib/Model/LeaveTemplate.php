<?php

namespace xepan\hr;

class Model_LeaveTemplate extends \xepan\base\Model_Table{
	public $table = "leave_template";
	function init(){
		parent::init();

		$this->addField('name');

		$this->hasMany('xepan\hr\Leave','leave_template_id',null,'Leave');
	}
}