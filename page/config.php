<?php

namespace xepan\hr;

class page_config extends \xepan\base\Page{
	public $title = "Employee Configuration";

	function init(){
		parent::init();
		$this->app->side_menu->addItem(['Salary Template','icon'=>'fa fa-percent'],'xepan_hr_salarytemplate')->setAttr(['title'=>'Salary Template']);
		// $this->app->side_menu->addItem(['Leave Template','icon'=>'fa fa-percent'],'xepan_hr_leavetemplate')->setAttr(['title'=>'Leave Template']);
		// $this->app->side_menu->addItem(['Official Holidays','icon'=>'fa fa-percent'],'xepan_hr_officialholidays')->setAttr(['title'=>'Official Holidays']);
			
	}
}