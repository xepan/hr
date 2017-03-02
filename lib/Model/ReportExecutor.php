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
	public $employee_array = [];
	public $widget_html;
	public $start_date;
	public $end_date;

	function init(){
		parent::init();
		
		$this->addField('employee')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('post')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('department')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$this->addField('widget')->display(['form'=>'xepan\base\NoValidateDropDown']);		
		$this->addField('starting_from_date')->type('date');
		$this->addField('schedule_date')->type('date');
		$this->addField('data_from_date')->type('date');
		$this->addField('data_to_date')->type('date');
		
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
									'01'=>'January',
									'02'=>'February',
									'03'=>'March',
									'04'=>'April',
									'05'=>'May',
									'06'=>'June',
									'07'=>'July',
									'08'=>'August',
									'09'=>'September',
									'10'=>'October',
									'11'=>'November',
									'12'=>'December'
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
		$this->addHook('beforeSave',[$this,'setInitialSchedule']);
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
		
		if($this['starting_from_date'] < $this->app->today)
			throw $this->exception('Starting date cannot be smaller then today','ValidityCheck')->setField('starting_from_date');	
		
		if(($this['time_span'] == 'Monthly') AND $this['data_range'] == null)
			throw $this->exception('Please select data range','ValidityCheck')->setField('data_range');	
		
		if(($this['time_span'] == 'Quarterly' OR $this['time_span'] == 'Halferly' OR $this['time_span'] == 'Yearly') AND $this['financial_month_start'] == null)
			throw $this->exception('Please select financial month start','ValidityCheck')->setField('financial_month_start');	
	}

	function setInitialSchedule(){
		if($this['schedule_date'] != null)
			return;

		$new_schedule = $this['starting_from_date'];
		switch ($this['time_span']) {
			case 'Daily':
				$new_data_from_date = date("Y-m-d", strtotime('- 1 day', strtotime($this['starting_from_date'])));
				$new_data_to_date = date("Y-m-d", strtotime('- 1 day', strtotime($this['starting_from_date'])));
				break;
			case 'Weekely':
				$new_data_from_date = date("Y-m-d", strtotime('- 1 Weeks', strtotime($this['starting_from_date'])));
				$new_data_to_date = date("Y-m-d", strtotime('- 0 day', strtotime($this['starting_from_date'])));
				break;
			case 'Fortnight':
				$new_data_from_date = date("Y-m-d", strtotime('- 2 Weeks', strtotime($this['starting_from_date'])));
				$new_data_to_date = date("Y-m-d", strtotime('- 0 day', strtotime($this['starting_from_date']))); 
				break;
			case 'Monthly':				
				if($this['data_range'] == 'Current'){
					$new_data_from_date =  date("Y-m-01", strtotime($this['starting_from_date']));
					$new_data_to_date = date("Y-m-t", strtotime($this['starting_from_date']));
				}else{
					$new_data_from_date = date("Y-m-01", strtotime('- 1 months', strtotime($this['starting_from_date'])));
					$new_data_to_date = date("Y-m-t", strtotime('- 1 months', strtotime($this['starting_from_date']))); 
				}
				break;
			case 'Quarterly':
				# code...
				break;
			case 'Halferly':
				# code...
				break;
			case 'Yearly':
				$previous_year = date("Y",strtotime("-1 year"));
				$new_data_from_date = date($previous_year."-".$this['financial_month_start']."-01",strtotime($this['starting_from_date']));
				$new_data_to_date = date("Y-m-t",strtotime($new_data_from_date));
				break;
			default:
				# code...
				break;
		}

		$this['schedule_date'] = $new_schedule;
		$this['data_from_date'] = $new_data_from_date;
		$this['data_to_date'] = $new_data_to_date;
		$this->save();
	}

	function upgradeSchedule($rpt){						
		$report = $rpt;
		switch ($report['time_span']) {
			case 'Daily':
				$new_schedule = date("Y-m-d", strtotime('+ 1 day', strtotime($report['schedule_date'])));				
				$new_data_from_date = date("Y-m-d", strtotime('- 1 day', strtotime($new_schedule)));
				$new_data_to_date = date("Y-m-d", strtotime('- 1 day', strtotime($new_schedule)));
				break;
			case 'Weekely':
				$new_schedule = date("Y-m-d", strtotime('+ 1 Weeks', strtotime($report['schedule_date'])));
				$new_data_from_date = date("Y-m-d", strtotime('- 1 Weeks', strtotime($new_schedule)));
				$new_data_to_date = date("Y-m-d", strtotime('- 0 day', strtotime($new_schedule)));
				break;
			case 'Fortnight':
				$new_schedule = date("Y-m-d", strtotime('+ 2 Weeks', strtotime($report['schedule_date'])));
				$new_data_from_date = date("Y-m-d", strtotime('- 2 Weeks', strtotime($new_schedule)));
				$new_data_to_date = date("Y-m-d", strtotime('- 0 day', strtotime($new_schedule))); 
				break;
			case 'Monthly':													
				$new_schedule = date("Y-m-d", strtotime('+ 1 months', strtotime($report['schedule_date'])));				
				
				if($report['data_range'] === 'Current'){					
					$new_data_from_date =  date("Y-m-01", strtotime($new_schedule));
					$new_data_to_date = date("Y-m-t", strtotime($new_schedule));
				}else{
					$new_data_from_date = date("Y-m-01", strtotime('- 1 months', strtotime($new_schedule)));
					$new_data_to_date = date("Y-m-t", strtotime('- 1 months', strtotime($new_schedule)));
				}
				break;
			case 'Quarterly':
				# code...
				break;
			case 'Halferly':
				# code...
				break;
			case 'Yearly':
				// $new_schedule = date("Y-m-d", strtotime('+ 12 months', strtotime($report['schedule_date'])));				
				// $previous_year = date("Y",strtotime("-1 year",strtotime($new_schedule)));				
				// $new_data_from_date = date($previous_year."-".$report['financial_month_start']."-01",strtotime($report['schedule_date']));
				// // BUGGY
				// $new_data_to_date = date("Y-m-t",strtotime($new_data_from_date));
				break;
			default:
				# code...
				break;
		}

		$report['schedule_date'] = $new_schedule;
		$report['data_from_date'] = $new_data_from_date;
		$report['data_to_date'] = $new_data_to_date;
		$report->saveAs('xepan\hr\Model_ReportExecutor');
	}

	function sendReport(){
		$report_executor_m = $this->add('xepan\hr\Model_ReportExecutor');
		$report_executor_m->addCondition('status','Active');
		$report_executor_m->addCondition('schedule_date',$this->app->today);

		foreach ($report_executor_m as $report) {			
			$this->employee_array = explode(',', $report['employee']);
			$post_array = explode(',', $report['post']);
			$department_array = explode(',', $report['department']);

			foreach ($post_array as $post){
				$this->findPostEmployees($post);
			}

			foreach ($department_array as $department){
				$this->findDepartmentEmployees($department);
			}

			$widget_array = explode(',', $report['widget']);

			foreach ($widget_array as $widget) {
				$this->extractHTML($widget,$report);
			}			

			$emails = [];
			foreach (array_unique($this->employee_array) as $employee) {
				$emp = $this->add('xepan\hr\Model_Employee')->load($employee);
				if($emp['status'] != 'Active') continue;
				array_push($emails, $emp['first_email']);
			} 

			$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
			$email_settings->addCondition('is_active',true);
			$email_settings->tryLoadAny();

			$mail = $this->add('xepan\hr\Model_ReportMail');	
			
			$email_subject = "Please find epan reports inside";			
			$email_body = $this->widget_html; 

			$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			foreach ($emails as  $email) {
				$mail->addTo($email);
			}
					
			$mail->setSubject($email_subject);
			$mail->setBody($email_body);
			
			// try{
			// 	$mail->send($email_settings);
			// }catch(\Exception $e){
			// 	throw $e;
			// }

			$this->upgradeSchedule($report);
		}
	}

	function findPostEmployees($post){
		$employee_m = $this->add('xepan\hr\Model_Employee');
		$employee_m->addCondition('post_id',$post);

		foreach ($employee_m as $emp) {
			$this->employee_array [] = $emp->id;
		}
	}

	function findDepartmentEmployees($department){
		$employee_m = $this->add('xepan\hr\Model_Employee');
		$employee_m->addCondition('department_id',$department);

		foreach ($employee_m as $emp) {
			$this->employee_array [] = $emp->id;
		}
	}

	function extractHTML($widget,$report){		
		$this->start_date = $report['data_from-date'];
		$this->end_date = $report['data_to_date'];

		$controller = $this->add('AbstractController');
		$html = $controller->add($widget,['report'=>$this])->getHTML();

		$this->widget_html .= $html."<br><br><br><hr>"; 
	}

	function enableFilterEntity(){
		// dummy function to let widget proceed
	}
}


/*
	Yearly 
	1. Current Year
		Depending Upon Financial Year
		starting = Y-fmonth-01
		end = starting + 12 months (y-m-l)

	2. Previous Year
		Depending Upon Financial Year
		starting = previous year - fmonth - 01
		ending	= starting = 12 months (y-m-l) 
*/