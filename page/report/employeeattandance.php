<?php

namespace xepan\hr;

class page_report_employeeattandance extends \xepan\base\Page{

	public $title = "Employee Attandance Report";

	function page_index(){
		// parent::init();
		
		$emp_id = $this->app->stickyGET('employee_id');
		$dept_id = $this->app->stickyGET('department_id');
		$from_date = $this->app->stickyGET('from_date')?:$this->app->today;
		$to_date = $this->app->stickyGET('to_date')?:$this->app->today;

		$form = $this->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelsCoppalsible(true)
			->layout([
				'date_range'=>'Filter~c1~3',
				'employee'=>'c2~3',
				'department'=>'c3~3',
				'FormButtons~&nbsp;'=>'c4~3'
			]);

		$date = $form->addField('DateRangePicker','date_range');
		$set_date = $this->app->today." to ".$this->app->today;
		if($from_date){
			$set_date = $from_date." to ".$to_date;
			$date->set($set_date);	
		}

		$emp_field = $form->addField('xepan\base\Basic','employee');
		$emp_field->setModel('xepan\hr\Model_Employee')->addCondition('status','Active');
		
		$dept_field = $form->addField('DropDown','department');
		$dept_field->setModel('xepan\hr\Model_Department');
		$dept_field->setEmptyText('please select department');
		
		$attandance_m = $this->add('xepan\hr\Model_Employee_Attandance',
							[
								'from_date'=>$from_date,
								'to_date'=>$to_date
							]);
		$attandance_m->addExpression('department_id')->set($attandance_m->refSQL('employee_id')->fieldQuery('department_id'));

		if($emp_id){
			$attandance_m->addCondition('employee_id',$emp_id);
		}
		if($dept_id)
			$attandance_m->addCondition('department_id',$dept_id);

		$form->addSubmit('Get Details')->addClass('btn btn-primary');
		$attandance_m->_dsql()->group('employee_id');
		
		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($attandance_m,['employee','total_in_time_login','total_out_time_login','averge_late_minutes','total_working_hours','extra_work','holidays_extra_work_hours','total_logout_before_official_time','total_logout_after_official_time']);

		if($form->isSubmitted()){
			$grid->js()->reload(
							[
								'employee_id'=>$form['employee'],
								'from_date'=>$date->getStartDate()?:0,
								'to_date'=>$date->getEndDate()?:0,
								'department_id'=>$form['department']?:0
							]
				)->execute();
		}

		/*In Time Formatter*/
		$grid->addFormatter('total_in_time_login','template')
			->setTemplate('<a href="#" class="intime_login" data-employee_id="{$employee_id}" data-from_date="'.$_GET['from_date'].'" data-to_date="'.$_GET['to_date'].'">{$total_in_time_login}</a>','total_in_time_login');
		$grid->js('click')->_selector('.intime_login')->univ()->frameURL('In Time Login Details',[$this->app->url('./intime_login'),'employee_id'=>$grid->js()->_selectorThis()->data('employee_id'),'from_date'=>$grid->js()->_selectorThis()->data('from_date'),'to_date'=>$grid->js()->_selectorThis()->data('to_date')]);

		/*After Time Formatter*/
		$grid->addFormatter('total_out_time_login','template')->setTemplate('<a href="#" class="outtime_login" data-employee_id="{$employee_id}" data-from_date="'.$_GET['from_date'].'" data-to_date="'.$_GET['to_date'].'">{$total_out_time_login}</a>','total_out_time_login');
		$grid->js('click')->_selector('.outtime_login')->univ()->frameURL('After Time Login Details',[$this->app->url('./outtime_login'),'employee_id'=>$grid->js()->_selectorThis()->data('employee_id'),'from_date'=>$grid->js()->_selectorThis()->data('from_date'),'to_date'=>$grid->js()->_selectorThis()->data('to_date')]);

		/*Avrage Late Hours Formatter*/
		$grid->addFormatter('averge_late_minutes','template')
				->setTemplate('<a href="#" class="avglateminutes" data-employee_id="{$employee_id}" data-from_date="'.$_GET['from_date'].'" data-to_date="'.$_GET['to_date'].'">{$averge_late_minutes}</a>','averge_late_minutes');
		$grid->js('click')->_selector('.avglateminutes')->univ()->frameURL('Avrage Late Minutes Details',[$this->app->url('./avrage_late'),'employee_id'=>$grid->js()->_selectorThis()->data('employee_id'),'from_date'=>$grid->js()->_selectorThis()->data('from_date'),'to_date'=>$grid->js()->_selectorThis()->data('to_date')]);
		
		/*Total Worknig Hours Formatter*/
		$grid->addFormatter('total_working_hours','template')->setTemplate('<a href="#" class="total_wh" data-employee_id="{$employee_id}" data-from_date="'.$_GET['from_date'].'" data-to_date="'.$_GET['to_date'].'">{$total_working_hours}</a>','total_working_hours');
		$grid->js('click')->_selector('.total_wh')->univ()->frameURL('Total Worknig Hours Details',[$this->app->url('./total_working_hours'),'employee_id'=>$grid->js()->_selectorThis()->data('employee_id'),'from_date'=>$grid->js()->_selectorThis()->data('from_date'),'to_date'=>$grid->js()->_selectorThis()->data('to_date')]);

		// extra work
		// $grid->addFormatter('extra_work','template')->setTemplate('<a href="#" class="extra_work" data-employee_id="{$employee_id}" data-from_date="'.$from_date.'" data-to_date="'.$to_date.'">{$extra_work}</a>','extra_work');	
		// $grid->js('click')->_selector('.extra_work')->univ()->frameURL('Extra Work Detail',[$this->app->url('./extra_work'),'employee_id'=>$grid->js()->_selectorThis()->data('employee_id'),'from_date'=>$grid->js()->_selectorThis()->data('from_date'),'to_date'=>$grid->js()->_selectorThis()->data('to_date')]);
	}

	function page_extra_work(){
		// $attandance = $this->add('xepan\hr\Model_Employee_Attandance');
		// $attandance->addCondition('employee_id',$_GET['employee_id'])
		// 			->addCondition('late_coming','<=',0)
		// 			->addCondition('from_date','>=',$_GET['from_date'])
		// 			->addCondition('to_date','<',$this->api->nextDate($_GET['to_date']));

		// $grid = $this->add('xepan\hr\Grid');
		// $grid->setModel($attandance,['from_date','to_date','official_day_start','official_day_end','working_hours','total_movement_in','total_movement_out']);
		// $grid->addPaginator(50);
	}

	function page_intime_login(){
		$attandance = $this->add('xepan\hr\Model_Employee_Attandance');
		$attandance->addCondition('employee_id',$_GET['employee_id'])
					->addCondition('late_coming','<=',0)
					->addCondition('from_date','>=',$_GET['from_date'])
					->addCondition('to_date','<',$this->api->nextDate($_GET['to_date']));

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($attandance,['from_date','to_date','official_day_start','official_day_end','working_hours','total_movement_in','total_movement_out']);
		$grid->addPaginator(50);
	}

	function page_outtime_login(){
		$attandance = $this->add('xepan\hr\Model_Employee_Attandance');
		$attandance->addCondition('employee_id',$_GET['employee_id'])
					->addCondition('late_coming','>',0)
					->addCondition('from_date','>=',$_GET['from_date'])
					->addCondition('to_date','<',$this->api->nextDate($_GET['to_date']));

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($attandance,['from_date','to_date','official_day_start','official_day_end','late_coming','working_hours','total_movement_in','total_movement_out']);
		$grid->addPaginator(50);

		$grid->addHook('formatRow',function($g){
			$late_coming = $g->model['late_coming'];
			if($g->model['late_coming'] > 60)
				$late_coming = $g->model['late_coming']/60; 
			$g->current_row_html['late_coming'] = round($late_coming,3);
		});
	}

	function page_avrage_late(){
		$attandance = $this->add('xepan\hr\Model_Employee_Attandance');
		$attandance->addCondition('employee_id',$_GET['employee_id'])
					->addCondition('from_date','>=',$_GET['from_date'])
					->addCondition('to_date','<',$this->api->nextDate($_GET['to_date']));

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($attandance,['from_date','to_date','official_day_start','official_day_end','late_coming','working_hours','total_movement_in','total_movement_out']);
		$grid->addPaginator(50);

		$grid->addHook('formatRow',function($g){
			$late_coming = $g->model['late_coming'];
			if($g->model['late_coming'] > 60)
				$late_coming = $g->model['late_coming']/60; 
			$g->current_row_html['late_coming'] = round($late_coming,3);
		});
	}

	function page_total_working_hours(){
		$attandance = $this->add('xepan\hr\Model_Employee_Attandance');
		$attandance->addCondition('employee_id',$_GET['employee_id'])
					->addCondition('from_date','>=',$_GET['from_date'])
					->addCondition('to_date','<',$this->api->nextDate($_GET['to_date']));

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($attandance,['from_date','to_date','official_day_start','official_day_end','late_coming','working_hours','total_movement_in','total_movement_out']);
		$grid->addPaginator(50);
	}
}