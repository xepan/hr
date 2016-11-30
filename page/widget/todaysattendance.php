<?php

namespace xepan\hr;

class page_widget_todaysattendance extends \xepan\base\Page{
	function init(){
		parent::init();

		$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendance_m->addCondition('fdate',$this->app->today);
			
		$grid = $this->add('xepan\hr\Grid',null,null,['page/widget/todaysattendance']);
		$grid->setModel($attendance_m,['employee_id','employee','fdate','late_coming','extra_work']);
		$grid->addPaginator(20);
		$grid->addQuickSearch('employee');

		$grid->js('click')->_selector('.digging-employee-attendance')->univ()->frameURL('Employee Movement',[$this->api->url('xepan_hr_widget_movement'),'emp_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
	}
}