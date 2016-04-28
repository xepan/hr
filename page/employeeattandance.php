<?php

namespace xepan\hr;

class page_employeeattandance extends \Page{
	public $title = "Employee Attandance";
	function init(){
		parent::init();

		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);
		$grid->setModel($movement,['employee','direction','time','reason','narration']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>40,'border'=>['width'=>0]],'name_field'=>'employee','default_value'=>'']);
	}
}