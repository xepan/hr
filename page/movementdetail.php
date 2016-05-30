<?php

namespace xepan\hr;

class page_movementdetail extends \xepan\base\Page{
	public $title = "Movement Detail";
	function init(){
		parent::init();
		
		$employee_id = $this->app->stickyGET('employee_id');
		$m = $this->add('xepan\hr\Model_Employee_Movement');
		$m->addCondition('employee_id',$employee_id);
		$m->addCondition('time','>=',$this->app->today);
		$m->addCondition('time','<',$this->app->nextDate($this->app->today));
		
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\movementdetail']);
		$grid->setModel($m);

		$grid->addPaginator(10);
		$grid->addQuickSearch(['direction']);
		
		$employee = $this->add('xepan\hr\Model_Employee')->load($employee_id);
		$grid->template->trySet('employee_name',$employee['name']);
	}
}