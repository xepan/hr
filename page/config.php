<?php

namespace xepan\hr;

class page_config extends \xepan\base\Page{
	public $title = "Employee Configuration";

	function init(){
		parent::init();
		// $this->app->side_menu->addItem(['Employee Attandance','icon'=>'fa fa-percent'],'xepan_hr_employeeattandance')->setAttr(['title'=>'Employee Attandance']);
			
		$this->app->side_menu->addItem(['Pay Slip Layouts','icon'=>'fa fa-th'],'xepan_hr_layouts')->setAttr(['title'=>'Pay Slip Layouts']);
		$this->app->side_menu->addItem(['Salary Template','icon'=>'fa fa-percent'],'xepan_hr_salarytemplate')->setAttr(['title'=>'Salary Template']);
		$this->app->side_menu->addItem(['Leave Template','icon'=>'fa fa-percent'],'xepan_hr_leavetemplate')->setAttr(['title'=>'Leave Template']);
		$this->app->side_menu->addItem(['Official Holidays','icon'=>'fa fa-calendar'],'xepan_hr_officialholiday')->setAttr(['title'=>'Official Holidays']);
		$this->app->side_menu->addItem(['Working Week Days','icon'=>'fa fa-calendar'],'xepan_hr_workingweekday')->setAttr(['title'=>'Working Week Days']);
		$this->app->side_menu->addItem(['Misc Config','icon'=>'fa fa-calendar'],'xepan_hr_miscconfig')->setAttr(['title'=>'Misc Config']);
	

	}
}