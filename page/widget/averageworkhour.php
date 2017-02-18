<?php

namespace xepan\hr;

class page_widget_averageworkhour extends \xepan\base\Page{
	function init(){
		parent::init();

		$x_axis = $this->app->stickyGET('x_axis');
		$details = $this->app->stickyGET('details');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');


		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendances->addCondition('employee',$x_axis);
		$attendances->setOrder('fdate','desc');

		if($from_date){
			$attendances->addCondition('fdate','>=',$from_date);
			$attendances->addCondition('fdate','<',$this->app->nextDate($to_date));
		}

		if($_GET['start_date'])
			$attendances->addCondition('fdate','>=',$_GET['start_date']);
			
		if($_GET['end_date'])
			$attendances->addCondition('fdate','<',$this->app->nextDate($_GET['end_date']));

		$grid = $this->add('xepan\hr\Grid',null,null,['page\widget\averagework']);
		$grid->setModel($attendances,['employee','fdate','working_hours']);

		$grid->addQuickSearch(['fdate']);
		$grid->addPaginator(20);

		$form = $grid->add('Form',null,'grid_buttons',['form\horizontal']);
		$date_range_field = $form->addField('DateRangePicker','date_range','')
								 ->setStartDate($this->app->now)
								 ->setEndDate($this->app->now)
								 ->getBackDatesSet();
		$form->addSubmit('Filter')->addClass('btn btn-sm btn-primary btn-block');
		
		if($form->isSubmitted()){
			$form->js(null,$grid->js()->reload(['from_date'=>$date_range_field->getStartDate(),'to_date'=>$date_range_field->getEndDate()]))->univ()->successMessage('wait ... ')->execute();
		}
	}
}