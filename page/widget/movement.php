<?php

namespace xepan\hr;

class page_widget_movement extends \xepan\base\Page{
	function init(){
		parent::init();

		
		$on_date = $this->app->stickyGET('on_date');
		$employee_id = $this->app->stickyGET('emp_id');

		$movement_m = $this->add('xepan\hr\Model_Employee_Movement');
		$movement_m->addCondition('employee_id',$employee_id);
		
		if($on_date){
			$movement_m->addCondition('movement_at','>=',$on_date);
			$movement_m->addCondition('movement_at','<',$this->app->nextDate($on_date));
		}else{
			$movement_m->addCondition('movement_at','>=',$this->app->today);
		}

		$movement_m->addExpression('next_movement_time')->set(function($m,$q){
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
		
		$movement_m->addExpression('next_movement_direction')->set(function($m,$q){
			$next_movement = $this->add('xepan\hr\Model_Employee_Movement',['table_alias'=>'next_movement'])
						 	->addCondition('employee_id',$m->getElement('employee_id'))
						 	->addCondition('movement_at','>',$m->getElement('movement_at'))
						 	->addCondition('date',$m->getElement('date'))
		                 	->setLimit(1);
		    return $next_movement->fieldQuery('direction');
		});

		$movement_m->addExpression('duration')->set(function($m,$q){
			return $q->expr('(TIMEDIFF([0],[1]))',[$m->getElement('next_movement_time'),$m->getElement('movement_at')]);
		});

		$grid = $this->add('xepan\hr\Grid',null,null,['page\widget\movement']);
		$grid->setModel($movement_m);

		$grid->addPaginator(10);
		$grid->addQuickSearch(['direction']);
		
		$employee = $this->add('xepan\hr\Model_Employee')->load($employee_id);
		$grid->template->trySet('employee_name',$employee['name']);

		$grid->addColumn('Duration');
		$grid->addMethod('format_timeduration',function($grid,$field){				
			$grid->current_row_html['duration'] = $grid->model['duration'];
		});

		$grid->addFormatter('Duration','timeduration');
	}
}