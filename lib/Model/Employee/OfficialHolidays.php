<?php

namespace xepan\hr;

class Model_Employee_OfficialHolidays extends \xepan\base\Model_Table{
	public $table =  "official_holidays";

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('date')->type('datetime');
		$this->addField('narration')->type('text');
	}
}