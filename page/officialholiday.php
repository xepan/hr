<?php

namespace xepan\hr;

class page_officialholiday extends \xepan\hr\page_config{
	public $title="Official Holidays";
	function init(){
		parent::init();

		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');

		$crud = $this->add('xepan\hr\CRUD',null,null,['page/config/officialholiday']);
		$crud->setModel($holiday_model);//,['name','from_date','to_date','type']);
	}
}