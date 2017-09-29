<?php

namespace xepan\hr;

class page_movementdetail extends \xepan\base\Page{
	public $title = "Movement Detail";
	public $breadcrumb=['Home'=>'index','Movement'=>'xepan_hr_employeemovement','Detail'=>'#'];
	function init(){
		parent::init();
		
		$employee_id = $this->app->stickyGET('employee_id');
		$movement_on = $this->app->stickyGET('movement_on')?:$this->app->today;

		$m = $this->add('xepan\hr\Model_Employee_Movement');
		$m->addCondition('employee_id',$employee_id);
		$m->addCondition('movement_at','>=',$movement_on);
		$m->addCondition('movement_at','<',$this->app->nextDate($movement_on));

		$m->addExpression('next_movement_time')->set(function($m,$q){
			$next_movement = $this->add('xepan\hr\Model_Employee_Movement',['table_alias'=>'next_movement'])
						 	->addCondition('employee_id',$m->getElement('employee_id'))
						 	->addCondition('movement_at','>',$m->getElement('movement_at'))
						 	->addCondition('date',$m->getElement('date'))
		                 	->setLimit(1);
		    return $q->expr('IFNULL([0],CONCAT([1]," ",[2]))',
		    			[
			    			$next_movement->fieldQuery('movement_at'),
			    			$m->getElement('date'),
			    			$m->getElement('employee_out_time')
			    		]
		    		);
		});
		
		$m->addExpression('next_movement_direction')->set(function($m,$q){
			$next_movement = $this->add('xepan\hr\Model_Employee_Movement',['table_alias'=>'next_movement'])
						 	->addCondition('employee_id',$m->getElement('employee_id'))
						 	->addCondition('movement_at','>',$m->getElement('movement_at'))
						 	->addCondition('date',$m->getElement('date'))
		                 	->setLimit(1);
		    return $next_movement->fieldQuery('direction');
		});

		$m->addExpression('duration')->set(function($m,$q){
			return $q->expr('(TIMEDIFF([0],[1]))',[$m->getElement('next_movement_time'),$m->getElement('movement_at')]);
		});

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($m,['direction','movement_at','duration']);

		$grid->addPaginator(50);
		$grid->addQuickSearch(['direction']);
		
		$employee = $this->add('xepan\hr\Model_Employee')->load($employee_id);
		$grid->template->trySet('employee_name',$employee['name']);
		$grid->addSno();
		// $grid->addColumn('Duration');
		// $grid->addMethod('format_timeduration',function($grid,$field){				
		// 	$grid->current_row_html['duration'] = $grid->model['duration'];
		// });
		// $grid->addFormatter('Duration','timeduration');
	}
}