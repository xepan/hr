<?php

namespace xepan\hr;

class page_widget_todaysattendance extends \xepan\base\Page{
	function init(){
		parent::init();

		$department_id = $this->app->stickyGET('department_id');
		
		$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendance_m->addExpression('department_id')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);
			return $emp->fieldQuery('department_id');
		});

		$attendance_m->addCondition('fdate',$this->app->today);
		
		if($department_id)
			$attendance_m->addCondition('department_id',$department_id);

		$grid = $this->add('xepan\hr\Grid',null,null,['page/widget/todaysattendance']);
		$grid->setModel($attendance_m,['employee_id','employee','fdate','late_coming','extra_work']);
		$grid->addPaginator(20);
		$grid->addQuickSearch('employee');
		$grid->addFormatter('extra_work','gmdate');

		$grid->js('click')->_selector('.digging-employee-attendance')->univ()->frameURL('Employee Movement',[$this->api->url('xepan_hr_widget_movement'),'emp_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
	}
}