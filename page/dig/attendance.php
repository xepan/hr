<?php

namespace xepan\hr;

class page_dig_attendance extends \xepan\base\Page{
	function init(){
		parent::init();

		$employee_id = $this->app->stickyGET('employee_id');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');

		$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendance_m->addCondition('employee_id',$employee_id);
		$attendance_m->setOrder('fdate','desc');

		if($from_date){
			$attendance_m->addCondition('fdate','>',$from_date);
			$attendance_m->addCondition('fdate','<',$this->app->nextDate($to_date));
		}
			
		$grid = $this->add('xepan\hr\Grid',null,null,['page/dig/attendance']);
		$grid->setModel($attendance_m,['fdate','late_coming','extra_work']);
		$grid->addPaginator(20);
		$grid->addQuickSearch('fdate');

		$form = $grid->add('Form',null,'grid_buttons',['form\horizontal']);
		$date_range_field = $form->addField('DateRangePicker','date_range','')
								 ->setStartDate($this->app->now)
								 ->setEndDate($this->app->now)
								 ->getBackDatesSet();
		$form->addSubmit('Filter')->addClass('btn btn-sm btn-primary btn-block');
		
		if($form->isSubmitted()){
			$form->js(null,$grid->js()->reload(['from_date'=>$date_range_field->getStartDate(),'to_date'=>$date_range_field->getEndDate()]))->univ()->successMessage('wait ... ')->execute();
		}

		$grid->js('click')->_selector('.digging-employee-attendance')->univ()->frameURL('Employee Movement',[$this->api->url('xepan_hr_dig_movement'),'employee_id'=>$employee_id,'on_date'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
	}
}