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
		$this->addField('starting_from_date')->type('date');
		$this->addField('schedule_date')->type('date');
		
		$data_range_field = $this->addField('data_range');
		$data_range_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$data_range_field->setValueList(
								[
									'Current'=>'Current',
									'Previous'=>'Previous'
								]);

		$financial_month_start_field = $this->addField('financial_month_start');
		$financial_month_start_field->display(['form'=>'xepan\base\NoValidateDropDown']);
		$financial_month_start_field->setValueList(
								[
									'January'=>'January',
									'February'=>'February',
									'March'=>'March',
									'April'=>'April',
									'may'=>'May',
									'June'=>'June',
									'July'=>'July',
									'August'=>'August',
									'September'=>'September',
									'October'=>'October',
									'November'=>'November',
									'December'=>'December'
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
		
		$this->addField('type')->defaultValue('ReportExecutor');
		$this->addField('status')->defaultValue('Active');

		$this->addHook('beforeSave',[$this,'validateFields']);
		$this->addHook('beforeSave',[$this,'calculateScheduleDate']);
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

	function validateFields(){
		if($this['employee'] == null AND $this['post'] == null AND $this['department'] == null)
			throw $this->exception('Please select atleast one employee or post or department','ValidityCheck')->setField('employee');
		
		if($this['widget'] == null)
			throw $this->exception('Please select atleast one widget','ValidityCheck')->setField('widget');

		if($this['time_span'] == null)
			throw $this->exception('Please select a value','ValidityCheck')->setField('time_span');
		
		if($this['starting_from_date'] == null)
			throw $this->exception('Please fill starting date','ValidityCheck')->setField('starting_from_date');
		
		if(($this['time_span'] == 'Fortnight' OR $this['time_span'] == 'Monthly') AND $this['data_range'] == null)
			throw $this->exception('Please select data range','ValidityCheck')->setField('data_range');	
		
		if(($this['time_span'] == 'Quarterly' OR $this['time_span'] == 'Halferly' OR $this['time_span'] == 'Yearly') AND $this['financial_month_start'] == null)
			throw $this->exception('Please select financial month start','ValidityCheck')->setField('financial_month_start');	
	}

	function upgradeSchedule(){
		// daily  =  +1 day [start_date = -1 day, end-date = +0 day]
		// weekely = +7 day [start_date = -7 day, end-date = +0 day]
		// fortnight = +14 day [{depending on data_range}] 
		// monthly = +1 month [{depending on data_range}]
		// quarterly = +4 months [{depending on financial year} ]
		// halfyearly = +6 months [{depending on financial year}]
		// yearly = +12 months [{depending on financial yaer}]
	}

	function sendReport(){

	}
}