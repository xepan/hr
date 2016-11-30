<?php

namespace xepan\hr;

class page_widget_averageperformance extends \xepan\base\Page{
	public $grid;

	function init(){
		parent::init();

		$type = $this->app->stickyGET('type');

		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$emp_j = $attendances->join('employee.contact_id','employee_id');
		$emp_j->addField('department_id');
		
		$attendances->addExpression('emp_status')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);

			return $emp->fieldQuery('status'); 
		});

		$attendances->addExpression('department_name')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Department');
			$emp->addCondition('id',$m->getElement('department_id'));
			$emp->setLimit(1);

			return $emp->fieldQuery('name'); 
		});

		$attendances->addCondition('emp_status','Active');

		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('department_id');
		
		if($type == 'late'){
			$this->grid = $this->add('xepan\hr\Grid',null,null,['page\widget\averageperformance']);
			$this->grid->setModel($attendances,['department_name','department_id','avg_late']);
		}else{			
			$this->grid = $this->add('xepan\hr\Grid',null,null,['page\widget\averageperformance']);
			$this->grid->setModel($attendances,['department_name','department_id','avg_extra_work']);
		}

		$this->grid->addHook('formatRow',function($g)use($type){						
			if($type =='late'){
				$g->current_row_html['val'] = $g->model['avg_late'];
			}else{
				$g->current_row_html['val'] = $g->model['avg_extra_work'];
			}
		});

		$this->grid->addQuickSearch(['department_name']);
		$this->grid->addPaginator(10);	
		
		$this->grid->js('click')->_selector('.average-performance-digging')->univ()->frameURL('Employee Average Performance',[$this->api->url('xepan_hr_widget_employeeperformance'),'dept_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
	}
}