<?php

namespace xepan\hr;

class page_employeeattandance extends \xepan\base\Page{
	public $title ="Employee Attandance";

	function init(){
		parent::init();
		
		$attandance_on = $this->app->stickyGET('attandance_on')?:$this->app->today;
		$department_id = $this->app->stickyGET('department_id');

		$filter_form = $this->add('Form');
		$filter_form->add('xepan\base\Controller_FLC')
			// ->addContentSpot()
			->layout([
				'attandance_on'=>'Filter~c1~4',
				'department'=>'c2~4',
				'FormButtons~'=>'c3~4'
			]);

		$dept_model = $this->add('xepan\hr\Model_Department')->addCondition('status','Active');

		$filter_form->addField('DatePicker','attandance_on')->set($attandance_on);
		$field_department = $filter_form->addField('DropDown','department');
		$field_department->setModel($dept_model);
		$field_department->setEmptyText('All');
		$filter_form->addSubmit('Filter')->addClass('btn btn-primary');

		// attendance form
		$form = $this->add('Form',null,null,['form/empty']);
		if($filter_form->isSubmitted()){
			$form->js()->reload(['attandance_on'=>$filter_form['attandance_on'],'department_id'=>$filter_form['department']])->execute();
		}

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');
		if($department_id)
			$employee->addCondition('department_id',$department_id);

		$header= $form->add('Columns')->addClass('row');
		$c00=$header->addColumn(1)->addClass('col-md-2')->add('H4')->set('Present / Absent');
		$c11=$header->addColumn(5)->addClass('col-md-5')->add('H4')->set('Employee`s');
		$c22=$header->addColumn(3)->addClass('col-md-2')->add('H4')->set('In Time');
		$c33=$header->addColumn(3)->addClass('col-md-2')->add('H4')->set('Out Time');

		foreach ($employee as $emp) {
			$col= $form->add('Columns')->addClass('row');
			$c0=$col->addColumn(1)->addClass('col-md-2');
			$c1=$col->addColumn(5)->addClass('col-md-5');
			$c2=$col->addColumn(3)->addClass('col-md-2');
			$c3=$col->addColumn(3)->addClass('col-md-2');

			$is_present_field = $c0->addField('checkbox','is_present_'.$emp->id,'');
			$c1->addField('line','name_'.$emp->id)->set($emp['name'])->setAttr('disabled','disabled');
			$from_time_field = $c2->addField('TimePicker','in_time_'.$emp->id);
			$to_time_field = $c3->addField('TimePicker','out_time_'.$emp->id);
			
			$in_time = $emp['in_time'];
			$out_time = $emp['out_time'];

			$emp_attandance =  $this->add('xepan\hr\Model_Employee_Attandance');
			$emp_attandance->addCondition('employee_id' , $emp->id);
			$emp_attandance->addCondition('fdate', $attandance_on);
			$emp_attandance->tryLoadAny();
			
			if($emp_attandance->loaded()){
				$is_present_field->set(true);
				$in_time = $emp_attandance['from_date'];
				$out_time = $emp_attandance['to_date'];
			}

			$from_time_field->set(date("H:i:s",strtotime($in_time)));
			$from_time_field
				->setOption('showMeridian',false)
				->setOption('defaultTime',0)
				->setOption('minuteStep',1)
				->setOption('showSeconds',true)
				;

			$to_time_field->set(date("H:i:s",strtotime($out_time)));
			$to_time_field
				->setOption('showMeridian',false)
				->setOption('defaultTime',0)
				->setOption('minuteStep',1)
				->setOption('showSeconds',true);

		}

		$form->addSubmit('Take Attandance')->addClass('btn btn-info');

		if($form->isSubmitted()){

			foreach ($employee as $emp) {
				if($form['is_present_'.$emp->id]){
					if(!$form['in_time_'.$emp->id]){
						$form->displayError('in_time_'.$emp->id,'In Time Must be Define');
					}
					if(!$form['out_time_'.$emp->id]){
						$form->displayError('out_time_'.$emp->id,'Out Time Must be Define');
					}

					$emp_attandance = $this->add('xepan\hr\Model_Employee_Attandance');
					$emp_attandance->addCondition('employee_id' , $emp->id);
					$emp_attandance->addCondition('fdate', $attandance_on);
					$emp_attandance->tryLoadAny();
					
					$emp_attandance['from_date']  = $attandance_on." ".$form['in_time_'.$emp->id];
					$emp_attandance['to_date']  = $attandance_on." ".$form['out_time_'.$emp->id];
					$emp_attandance['is_holiday'] = $emp_attandance->isHoliday($attandance_on);
					$emp_attandance->save();
					
				}else{
					// if not present then remove values
					$del_emp_attandance =  $this->add('xepan\hr\Model_Employee_Attandance');
					$del_emp_attandance->addCondition('employee_id',$emp->id);
					$del_emp_attandance->addCondition('fdate', $attandance_on);
					$del_emp_attandance->tryLoadAny();
					if($del_emp_attandance->loaded()){
						$del_emp_attandance->delete();
					}
				}
			}

			$form->js(null,$form->js()->univ()->successMessage('Attandance Updated'))
				->reload()
				->execute();
		}
	}
}