<?php

namespace xepan\hr;

class Model_ReportExecutor extends \xepan\base\Model_Table{
	public $table='report_executor';
	public $acl=true;
	public $status = ['Active','InActive'];
	public $actions = [
						'Active'=>['view','edit','delete','deactivate'],
						'InActive'=>['view','edit','delete','activate']
					  ];

	function init(){
		parent::init();
		
		$this->addField('employee')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('post')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('department')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('widget')->display(['form'=>'xepan\base\NoValidateDropDown']);		
		$this->addField('starting_from_date');

		$data_range_field = $this->addField('data_range');
		$data_range_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$data_range_field->setValueList(
								[
									'Current'=>'Current',
									'Previous'=>'Previous'
								]);

		$month_end_field = $this->addField('month_end');
		$month_end_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$month_end_field->setValueList(
								[
									'28'=>'28',
									'30'=>'30',
									'31'=>'31'
								]);

		$post_at_field = $this->addField('post_at');
		$post_at_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$post_at_field->setValueList(
								[
									'MonthStart'=>'MonthStart',
									'MonthMid'=>'MonthMid',
									'MonthEnd'=>'MonthEnd'
								]);

		$financial_month_start_field = $this->addField('financial_month_start');
		$financial_month_start_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$financial_month_start_field->setValueList(
								[
									'Monday'=>'Monday',
									'Tuesday'=>'Tuesday',
									'Wednesday'=>'Wednesday',
									'Thrusday'=>'Thrusday',
									'Friday'=>'Friday',
									'Saturday'=>'Saturday',
									'Sunday'=>'Sunday'
								]);

		$time_span_field = $this->addField('time_span');
		$time_span_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$time_span_field->setValueList(
								[
									'Daily'=>'Daily',
									'Weekely'=>'Weekely',
									'Fortnight'=>'Fortnight',
									'Monthly'=>'Monthly',
									'Quarterly'=>'Quarterly',
									'Halferly'=>'Halferly',
									'Yearly'=>'Yearly'
								]);

		$day_field = $this->addField('day');
		$day_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$day_field->setValueList(
								[
									'Monday'=>'Monday',
									'Tuesday'=>'Tuesday',
									'Wednesday'=>'Wednesday',
									'Thrusday'=>'Thrusday',
									'Friday'=>'Friday',
									'Saturday'=>'Saturday',
									'Sunday'=>'Sunday'
								]);
		
		$this->addField('type')->defaultValue('ReportExecutor');
		$this->addField('status')->defaultValue('Active');
		
		$this->addHook('beforeSave',[$this,'validateFields']);
	}

	function validateFields(){
		if($this['employee'] == null AND $this['post'] == null AND $this['department'] == null)
			throw $this->exception('Please select atleast one employee or post or department','ValidityCheck')->setField('employee');
		
		if($this['widget'] == null)
			throw $this->exception('Please select atleast one value','ValidityCheck')->setField('widget');

		if($this['time_span'] == null)
			throw $this->exception('Please select a value','ValidityCheck')->setField('time_span');
		
		if($this['time_span'] === 'Weekely' AND $this['day'] == null)
			throw $this->exception('Please select a day','ValidityCheck')->setField('day');
		
		if($this['time_span'] === 'Fortnight' AND $this['data_range'] == null)
			throw $this->exception('Please select a data range','ValidityCheck')->setField('data_range');
		
		if(($this['time_span'] === 'Fortnight' OR $this['time_span'] === 'Monthly') AND $this['starting_from_date'] == null)
			throw $this->exception('Please enter a starting date','ValidityCheck')->setField('starting_from_date');
		
		if(($this['time_span'] === 'Monthly') AND $this['post_at'] == null)
			throw $this->exception('Please enter a posting time','ValidityCheck')->setField('post_at');
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Report Schedule : '".$this['time_span']."' Activated", null/* Related Document ID*/, $this->id,null,null,"xepan_hr_reportschedule")
            ->notifyWhoCan('deactivate','Active',$this);
		$this->save();
	}


	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Report Schedule : '".$this['time_span']."' Deactivated", null/* Related Document ID*/, $this->id,null,null,"xepan_hr_reportschedule")
            ->notifyWhoCan('activate','InActive',$this);
		$this->save();
	}
}