<?php

namespace xepan\hr;

class page_employeeattandancereport extends \xepan\base\Page{

	function init(){
		parent::init();

		$date = $this->app->today;
		if($_GET['selecteddate']){
			$date = $_GET['selecteddate'];
		}

		$form = $this->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelsCoppalsible(true)
			->layout([
				'month'=>'Filter~c1~4',
				'FormButtons~&nbsp;'=>'c2~4'
			]);

		$date_field = $form->addField('DatePicker','month')->validate('required');
		$date_field->options = [
						'format'=> "yyyy-MM",
						'startView'=>"months",
						'minViewMode'=>"months"
					];
		$date_field->set($date);
		$form->addSubmit('Filter')->addClass('btn btn-primary');

		$field_array = ['name','post','in_time','out_time'];
		$remove_field = [];
		for ($i=1; $i <= $this->app->getDaysInMonth($date); $i++){
			$field_array[] = 'attendance_of_'.$i;
			$field_array[] = 'in_time_of_'.$i;
			$field_array[] = 'out_time_of_'.$i;
			$remove_field[] = 'in_time_of_'.$i;
			$remove_field[] = 'out_time_of_'.$i;
		}
		$field_array[] = 'total_attendance';
				
		$att_model = $this->add('xepan\hr\Model_Employee_AttandanceData',['curr_date'=>$date]);
		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($att_model,$field_array);
		$grid->addHook('formatRow',function($g)use($date){
			for ($i=1; $i <= $this->app->getDaysInMonth($date); $i++) { 
				$g->current_row_html['attendance_of_'.$i] = $g->model['attendance_of_'.$i].'<br/> In - '.$g->model['in_time_of_'.$i].'<br/> Out - '.$g->model['out_time_of_'.$i];
			}
		});

		foreach ($remove_field as $key => $value) {
			$grid->removeColumn($value);
		}

		$grid->add('misc/Export',['export_fields'=>$field_array]);

		if($form->isSubmitted()){
			$form->js(null,$grid->js()->reload(['selecteddate'=>$form['month']]))->execute();
		}

	}
}