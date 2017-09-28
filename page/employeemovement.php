<?php

namespace xepan\hr;

class page_employeemovement extends \xepan\base\Page{
	public $title = "Employee Movement";
	function init(){
		parent::init();

		$date = $movement_on = $this->app->stickyGET('movement_on')?:$this->app->today;
		$department_id = $this->app->stickyGET('department_id');

		$filter_form = $this->add('Form');
		$filter_form->add('xepan\base\Controller_FLC')
			// ->addContentSpot()
			->layout([
				'movement_on'=>'Filter~c1~4',
				'department'=>'c2~4',
				'FormButtons~'=>'c3~4'
			]);

		$dept_model = $this->add('xepan\hr\Model_Department')->addCondition('status','Active');

		$filter_form->addField('DatePicker','movement_on')->set($movement_on);
		$field_department = $filter_form->addField('DropDown','department');
		$field_department->setModel($dept_model);
		$field_department->setEmptyText('All');
		if($department_id)
			$field_department->set($department_id);

		$filter_form->addSubmit('Filter')->addClass('btn btn-primary');
		
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);

		if($filter_form->isSubmitted()){
			$grid->js()->reload(['movement_on'=>$filter_form['movement_on'],'department_id'=>$filter_form['department']])->execute();
		}

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');
		if($department_id)
			$employee->addCondition('department_id',$department_id);

		$employee->addExpression('first_in')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','In')
					->setOrder('movement_at','asc')
					->setLimit(1)
					->fieldQuery('movement_at');
		});

		$employee->addExpression('last_out')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','Out')
					->setOrder('movement_at','desc')
					->setLimit(1)
					->fieldQuery('movement_at');
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
			  			->setOrder('movement_at','desc')
			  			->setLimit(1);

			return $q->expr('IFNULL([0],"Out")',[$temp->fieldQuery('direction')]);
		});

		$employee->addExpression('last_direction_today')->set(function($m,$q){
			$temp = $m->refSQL('EmployeeMovements')
						->addCondition('date',$this->app->today)
			  			->setOrder('movement_at','desc')
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
					"IF([0]<= CONCAT('[1]',' ',[2]),'success','danger')",
					  [
						$m->getElement('first_in'),
						$date,
						$m->getElement('in_time')
					  ]
					);
		});

		$employee->addExpression('out_color')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]>= CONCAT('[1]',' ',[2]),'success','danger')",
					  [
						$m->getElement('last_out'),
						$date,
						$m->getElement('out_time')
					  ]
					);
		});

		$grid->setModel($employee,['name','first_in','last_out','in_color','out_color','is_late','is_out']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>50,'border'=>['width'=>0]],'name_field'=>'name','default_value'=>'']);
		$grid->addPaginator(50);
		$frm=$grid->addQuickSearch(['name']);

		$grid->addColumn('In/Out');
		$grid->addSno();
		$grid->js('click')->_selector('.do-view-employee-movement')->univ()->frameURL('Employee Movements',[$this->api->url('xepan_hr_movementdetail'),'employee_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'),'movement_on'=>$movement_on]);
	}
}



