<?php

namespace xepan\hr;

class Widget_DepartmentLateComing extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('department');
		$this->view = $this->add('View',null,null,['view\multibox']);
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendances->addExpression('emp_department')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);
			return $emp->fieldQuery('department_id');
		});

		$attendances->addExpression('emp_status')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);
			return $emp->fieldQuery('status');
		});

		$attendances->addCondition('emp_status','Active');

		if(isset($this->report->department)){			
			$attendances->addCondition('emp_department',$this->report->department);
		}else{
			$attendances->addCondition('emp_department',$this->app->employee['department_id']);
		}

		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('employee_id');

		$total_avg_late = 0;
		$total_extra_work = 0;
		foreach ($attendances as $att){
			$total_avg_late += $att['avg_late'];
			$total_extra_work += $att['avg_extra_work'];
		}
		
		$this->view->template->trySet('value1',$total_avg_late);
		$this->view->template->trySet('value2',$total_extra_work);

		if(isset($this->report->department))
			$dept_id = $this->report->department;
		else
			$dept_id = null;

		$this->view->js('click')->_selector('.box-promptness')->univ()->frameURL('Department Promptness',[$this->api->url('xepan_hr_widget_employeeperformance'),'dept_id'=>$dept_id]);
		
		return parent::recursiveRender();
	}
}