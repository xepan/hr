<?php 

namespace xepan\hr;

class Widget_EmployeeMovement extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->report->enableFilterEntity('employee');

	}

	function recursiveRender(){

		$movement_m = $this->add("xepan\hr\Model_Employee_Movement");
		if(isset($this->report->employee))
	        $movement_m->addCondition('employee_id',$this->report->employee);
		if(isset($this->report->start_date))
			$movement_m->addCondition('date','>',$this->report->start_date);
		if(isset($this->report->end_date))
			$movement_m->addCondition('date','<',$this->app->nextDate($this->report->end_date));

		// $movement_m->_dsql()
					// ->group(['direction',$movement_m->_dsql()->expr('[0]',[$movement_m->getElement('date')])]);
		$data_array = [];
		
		// foreach ($movement_m as $model) {
		// 	if(isset($data_array[$model['date']])) $data_array[$model['date']]=[];
		// 	$data_array[$model['date']] = array_merge($data_array[$model['date']],['date'=>$model['date'], $model['direction']=>$model['direction']]);
		// }
		// $data_array = array_values($data_array);

		$this->add('xepan\base\View_Chart')
			->setType('bar')
	 		->setData(['json'=>$data_array])
	 		->setGroup(['In','Out'])
	 		->setXAxis('date')
	 		->setYAxises(['In','Out'])
	 		->addClass('col-md-12')
	 		->setTitle('Employee Movement')
	 		;

		return parent::recursiveRender();
	}
}