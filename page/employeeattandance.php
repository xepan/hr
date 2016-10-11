<?php

namespace xepan\hr;

class page_employeeattandance extends \xepan\base\Page{
	public $title ="Employee Attandance";

	function init(){
		parent::init();
		$employee = $this->add('xepan\hr\Model_Employee');
		$form=$this->add('Form',null,null,['form/empty']);

		foreach ($employee as $emp) {

			$col= $form->add('Columns')->addClass('row');
			$c0=$col->addColumn(1)->addClass('col-md-1');
			$c1=$col->addColumn(5)->addClass('col-md-5');
			$c2=$col->addColumn(3)->addClass('col-md-3');
			$c3=$col->addColumn(3)->addClass('col-md-3');

			$c0->addField('checkbox','is_present_'.$emp->id,'');
			$c1->addField('line','name_'.$emp->id)->set($emp['name']);
			$from_time_field = $c2->addField('TimePicker','in_time_'.$emp->id)->set($emp['in_time']);
			$from_time_field
				->setOption('showMeridian',false)
				->setOption('defaultTime',1)
				->setOption('minuteStep',1)
				->setOption('showSeconds',true);
			$to_time_field = $c3->addField('TimePicker','out_time_'.$emp->id)->set($emp['out_time']);
			$to_time_field
				->setOption('showMeridian',false)
				->setOption('defaultTime',1)
				->setOption('minuteStep',1)
				->setOption('showSeconds',true);
		}

		$form->addSubmit('Take Attandance')->addClass('btn btn-info');

		if($form->isSubmitted()){

			foreach ($employee as $emp) {
				if($form['is_present_'.$emp->id]){
					// throw new \Exception($this->app->today." ".$form['in_time_'.$emp->id], 1);
					if(!$form['in_time_'.$emp->id]){
						$form->displayError('in_time_'.$emp->id,'In Time Must be Define');
					}

					$emp['attandance_mode'] = "Mannual";					
					$emp['in_time'] = $form['in_time_'.$emp->id];
					$emp['out_time'] = $form['out_time_'.$emp->id];
					$emp->save();

					$emp_attandance =  $this->add('xepan\hr\Model_Employee_Attandance');
					$emp_attandance->addCondition('employee_id' , $emp->id); 
					$emp_attandance->addCondition('fdate', $this->app->today);
					$emp_attandance->tryLoadAny();

					if(!$emp_attandance->loaded()){
						$emp_attandance['employee_id'] = $emp->id;
						$emp_attandance['from_date']  = $this->app->today." ".$form['in_time_'.$emp->id];
						$emp_attandance->save();
					}
				}

				if(!$form['is_present_'.$emp->id]){
					$del_emp_attandance =  $this->add('xepan\hr\Model_Employee_Attandance');
					$del_emp_attandance->addCondition('employee_id',$emp->id);
					$del_emp_attandance->addCondition('fdate', $this->app->today);
					$del_emp_attandance->tryLoadAny();
					if($del_emp_attandance->loaded()){
						$del_emp_attandance->delete();						
					}
				}
			}
		}
	}
}