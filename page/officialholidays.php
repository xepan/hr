<?php
namespace xepan\hr;

class page_officialholidays extends \xepan\hr\page_configurationsidebar{
	public $title = "Official Holidays";

	function init(){
		parent::init();
		
		$official_holidays_m = $this->add('xepan\hr\Model_OfficialHolidays');
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($official_holidays_m);
	}
}