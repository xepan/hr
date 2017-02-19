<?php

namespace xepan\hr;

class page_widget_todaysattendance extends \xepan\base\Page{
	function init(){
		parent::init();

		$department_id = $this->app->stickyGET('department');

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
		else{
			$attendance_m->addCondition('department_id',$this->app->employee['department_id']);
		}

		$grid = $this->add('xepan\hr\Grid',null,null,['page/widget/todaysattendance']);
		$grid->setModel($attendance_m,['employee_id','employee','fdate','late_coming','extra_work']);
		$grid->addPaginator(25);
		$grid->addQuickSearch('employee');

		$grid->js('click')->_selector('.digging-employee-attendance')->univ()->frameURL('Employee Movement',[$this->api->url('xepan_hr_widget_movement'),'emp_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		
		$grid->addHook('formatRow',function($g){			
			if($g->model['late_coming'] < 0 )
				$g->current_row_html['late_coming'] = abs($g->model['late_coming']).' Minutes Early';
			else	
				$g->current_row_html['late_coming'] = abs($g->model['late_coming']).' Minutes Late';
			
			if($g->model['extra_work'] < 0 )
				$g->current_row_html['extra_work'] = 'No Logout Registration';
			else
				$g->current_row_html['extra_work'] = abs($g->model['extra_work']).' Minutes';
		});
	}
}