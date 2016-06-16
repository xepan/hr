<?php

namespace xepan\hr;

class page_employeemovement extends \xepan\base\Page{
	public $title = "Employee Movement";
	function init(){
		parent::init();

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');
		$date = $this->app->today;
		
		$employee->addExpression('first_in')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','In')
					->setOrder('time','asc')
					->setLimit(1)
					->fieldQuery('time');
		});

		$employee->addExpression('last_out')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','Out')
					->setOrder('time','desc')
					->setLimit(1)
					->fieldQuery('time');
		});


		$employee->addExpression('is_late')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]>= CONCAT('[1]',' ',[2]),1,0)",
					  [
						$m->getElement('first_in'),
						$date,
						$m->getElement('in_time')
					  ]
					);
		});

		$employee->addExpression('last_direction')->set(function($m,$q){
			$temp = $m->refSQL('EmployeeMovements')
			  			->setOrder('time','desc')
			  			->setLimit(1);

			return $q->expr('IFNULL([0],"Out")',[$temp->fieldQuery('direction')]);
		});

		$employee->addExpression('last_direction_today')->set(function($m,$q){
			$temp = $m->refSQL('EmployeeMovements')
						->addCondition('date',$this->app->today)
			  			->setOrder('time','desc')
			  			->setLimit(1);

			return $q->expr('IFNULL([0],"Out")',[$temp->fieldQuery('direction')]);
		});

		
		$employee->addExpression('is_in')->set(function($m,$q){
			return $q->expr(
					"IF([0]='In' AND [1]=	[2],'In','Out')",
					  [
						$m->getElement('last_direction'),
						$m->getElement('date'),
					  	$this->app->today
					  ]
					);
		});

		$employee->addExpression('is_out')->set(function($m,$q){
			return $q->expr(
					"IF([0]='Out','Out','In')",
					  [
						$m->getElement('last_direction_today'),
					  ]
					);
		});
		
		$employee->addExpression('in_color')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]<= CONCAT('[1]',' ',[2]),'primary','danger')",
					  [
						$m->getElement('first_in'),
						$date,
						$m->getElement('in_time')
					  ]
					);
		});

		$employee->addExpression('out_color')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]>= CONCAT('[1]',' ',[2]),'primary','danger')",
					  [
						$m->getElement('last_out'),
						$date,
						$m->getElement('out_time')
					  ]
					);
		});

		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);
		$grid->setModel($employee,['name','first_in','last_out','in_color','out_color','is_late','is_out']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>50,'border'=>['width'=>0]],'name_field'=>'name','default_value'=>'']);
		$grid->addPaginator(50);
		$frm=$grid->addQuickSearch(['employee']);

		$grid->addColumn('In/Out');

		$grid->js('click')->_selector('.view-frame')->univ()->frameURL('Employee Movements',[$this->api->url('xepan_hr_movementdetail'),'employee_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
	}
}



