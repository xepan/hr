<?php

namespace xepan\hr;

class Widget_TotalLateComing extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('Department');
		$this->view = $this->add('View',null,null,['view\multibox']);
	}

	function recursiveRender(){
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		$attendances->addExpression('emp_status')->set(function($m,$q){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->addCondition('id',$m->getElement('employee_id'));
			$emp->setLimit(1);
			return $emp->fieldQuery('status');
		});

		$attendances->addCondition('emp_status','Active');
		
		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])',[$attendances->getElement('early_leave')]));
		$attendances->_dsql()->group('employee_id');

		$total_avg_late = 0;
		$total_extra_work = 0;
		foreach ($attendances as $att){
			if($att['avg_extra_work'] < 0 )
				$att['avg_extra_work'] = 0;

			if($att['avg_late'] < 0 )
				$att['avg_late'] = 0;

			$total_avg_late += $att['avg_late'];
			$total_extra_work += $att['avg_extra_work'];
		}
		
		$this->view->template->trySet('value1',$total_avg_late);
		$this->view->template->trySet('value2',$total_extra_work);

		
		$this->view->js('click')->_selector('.box-promptness')->univ()->frameURL('Department Promptness',[$this->api->url('xepan_hr_widget_averageperformance')]);
		
		return parent::recursiveRender();
	}
}