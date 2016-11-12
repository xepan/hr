<?php 

namespace xepan\hr;

class Widget_EmployeeAttendance extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('employee');

	}

	function recursiveRender(){
		
		$attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
        $attan_m->addCondition('fdate',$this->app->today);
		
		$emp_model = $this->add('xepan\hr\Model_Employee');

		if(isset($this->report->employee)){
	        $attan_m->tryLoadBy('employee_id',$this->report->employee);
	        $emp_model->load($this->report->employee);
		}else{
	        $attan_m->tryLoadBy('employee_id',$this->app->employee->id);
	        $emp_model->load($this->app->employee->id);
		}

		$emp_name = $emp_model['name'];

        $view = $this->add('xepan\base\View_Widget_SingleInfo');

		if(!$attan_m->loaded()){
			$view->setIcon('fa fa-thumbs-down')
                    ->setHeading(strtoupper($emp_name).' IS NOT PRESENT')
                    ->setValue('-')
                    ->makeDanger()
                    ;
		}else{
	        if($attan_m['late_coming']>0){
	        	$view->setIcon('fa fa-thumbs-down')
                    ->setHeading(date('h:i A', strtotime($attan_m['from_date'])).' ! '.strtoupper($emp_name).' IS LATE BY ')
                    ->setValue($attan_m['late_coming'].' Minutes')
                    ->makeDanger()
                    ;
	        }else{
	        	$view->setIcon('fa fa-thumbs-up')
                    ->setHeading(date('h:i A', strtotime($attan_m['from_date'])).' ! '.strtoupper($emp_name).' IS EARLY BY ')
                    ->setValue(abs($attan_m['late_coming']).' Minutes')
                    ->makeSuccess()
                    ;
	        }
		}
		return parent::recursiveRender();
	}
}