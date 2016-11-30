<?php 

namespace xepan\hr;

class Widget_DepartmentEmployeeAttendance extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('department');
		$this->grid = $this->add('xepan\hr\Grid',null,null,['view\employee\movement-mini']);
	}

	function recursiveRender(){
		$attendance_m = $this->add('xepan\hr\Model_Employee');
		
		if(isset($this->report->department)){
			$attendance_m->addCondition('department_id',$this->report->department);
		}else{
			$attendance_m->addCondition('department_id',$this->app->employee->id);
		}

		$attendance_m->addExpression('from_date')->set(function($m,$q){
			$att = $this->add('xepan\hr\Model_Employee_Attandance');
			$att->addCondition('employee_id',$m->getElement('id'))
				->addCondition('fdate',$this->app->today)
				->setLimit(1);

			return $att->fieldQuery('from_date');
		});

		$attendance_m->addExpression('late_coming')->set(function($m,$q){
			$att = $this->add('xepan\hr\Model_Employee_Attandance');
			$att->addCondition('employee_id',$m->getElement('id'))
				->addCondition('fdate',$this->app->today)
				->setLimit(1);

			return $att->fieldQuery('late_coming');
		});

		$attendance_m->setOrder('late_coming','desc');

		$this->grid->setModel($attendance_m,['name','from_date','late_coming']);
		$this->grid->addPaginator(50);
		
		$this->grid->addHook('formatRow',function($g){
			if($g->model['from_date']== null)
				$g->current_row_html['in_at'] = 'Not In';
			else	
				$g->current_row_html['in_at'] = date('h:i A', strtotime($g->model['from_date']));
			
			if($g->model['late_coming']>0){
				$g->current_row_html['icon-class'] = 'fa fa-thumbs-o-down';
				$g->current_row_html['text-class'] = 'red';
			}
			else{
				$g->current_row_html['icon-class'] = 'fa fa-thumbs-o-up';
				$g->current_row_html['text-class'] = 'green';
			}

			if($g->model['from_date']== null)
				$g->current_row_html['text-class'] = 'gray';
			else	
				$g->current_row_html['dummy'] = ' ';
		});

		return parent::recursiveRender();
	}
}