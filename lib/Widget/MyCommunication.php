<?php

namespace xepan\hr;

class Widget_MyCommunication extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->grid = $this->add('xepan\hr\Grid');
	}

	function recursiveRender(){

		$communication_model = $this->add('xepan\communication\Model_Communication');
		$communication_model->addExpression('date','date(created_at)');
		$communication_model->addCondition([['from_id',$this->app->employee->id],['to_id',$this->app->employee->id]]);
		$communication_model->addCondition('communication_type','<>',['Newsletter','ReminderEmail']);
		
		if(isset($this->report->start_date))
			$communication_model->addCondition('created_at','>',$this->report->start_date);
		if(isset($this->report->end_date))
			$communication_model->addCondition('created_at','<',$this->app->nextDate($this->report->end_date));
		
		$this->grid->setModel($communication_model,['to','title','communication_type','created_at']);
		$this->grid->addPaginator(10);

		$this->grid->add('H2',null,'grid_buttons')->set('My Communications')->addClass('text-muted');
		$this->grid->removeSearchIcon();

		return parent::recursiveRender();
	}
}


