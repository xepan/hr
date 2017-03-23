<?php

namespace xepan\hr;

/**
* 
*/
class Model_Employee_Attandance extends \xepan\base\Model_Table{
	public $table = "employee_attandance";
	public $acl = false;
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('from_date')->type('datetime');
		$this->addField('to_date')->type('datetime')->defaultValue(null);
		$this->addField('is_holiday')->type('boolean');
		$this->addField('working_unit_count');

		$this->addExpression('fdate')->set('DATE(from_date)');
		$this->addExpression('tdate')->set('DATE(to_date)');

		$this->addExpression('official_day_start')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('in_time')
				]);
		});

		$this->addExpression('official_day_end')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('out_time')
				]);
		});

		$this->addExpression('actual_day_ending')->set(function($m,$q){
			return $q->expr('IFNULL([0],[1])',[
										$q->getField('to_date'),
										$m->getElement('official_day_end')
									]
							);
		});

		$this->addExpression('actual_day_start_time')->set('date_format(from_date,"%H:%i:%s")');
		$this->addExpression('actual_day_end_time')->set(function($m,$q){
			return $q->expr('date_format([0],"%H:%i:%s")',[
										$m->getElement('actual_day_ending')
										]
							);

		});

		// $this->addExpression('late_coming')->set(function($m,$q){
		// 	return $q->expr('TIMESTAMPDIFF(MINUTE,[0],[1])',[
		// 			$m->getElement('official_day_start'),
		// 			$q->getField('from_date'),
		// 		]);
		// });

		$this->addExpression('late_coming')->set(function($m,$q){
			return $q->expr(
					"IF([is_holiday]='1','0',TIMESTAMPDIFF(MINUTE,[official_day_start],[from_date]))",
						[
							'official_day_start'=>$m->getElement('official_day_start'),
							'from_date'=>$m->getElement('from_date'),
							'is_holiday'=>$m->getElement('is_holiday')
						]);
		});

		// $this->addExpression('extra_work')->set(function($m,$q){
		// 	return $q->expr('TIMESTAMPDIFF(MINUTE,[1],[0])',[
		// 			$m->getElement('actual_day_ending'),
		// 			$m->getElement('official_day_end'),
		// 		]);
		// });

		$this->addExpression('extra_work')->set(function($m,$q){
			return $q->expr(
					"IF([is_holiday]='1',
						TIMESTAMPDIFF(MINUTE,[from_date],[actual_day_ending]),
						TIMESTAMPDIFF(MINUTE,[official_day_end],[actual_day_ending]))",
						[
							'official_day_end'=>$m->getElement('official_day_end'),
							'actual_day_ending'=>$m->getElement('actual_day_ending'),
							'from_date'=>$m->getElement('from_date'),
							'is_holiday'=>$m->getElement('is_holiday')
						]);
		});

		$this->addExpression('working_hours')->set(function($m,$q){
			return $q->expr('TIMESTAMPDIFF(HOUR,[0],[1])',[
					$q->getField('from_date'),
					$m->getElement('actual_day_ending'),
				]);
		});
	}

	function isHoliday($today){
		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');
		$holiday_model->addCondition('from_date','>=',$today);
		$holiday_model->addCondition('to_date','<=',$today);

		if($holiday_model->tryLoadAny()->loaded())
			return 1;

		$week_day_model = $this->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'monday'=>"checkbox",
										'tuesday'=>"checkbox",
										'wednesday'=>"checkbox",
										'thursday'=>"checkbox",
										'friday'=>"checkbox",
										'saturday'=>"checkbox",
										'sunday'=>"checkbox"
										],
							'config_key'=>'HR_WORKING_WEEK_DAY',
							'application'=>'hr'
						]);
		$week_day_model->tryLoadAny();
		
		$day = strtolower(date("l", strtotime($this->app->now)));
	
		if($week_day_model[$day])
			return 0;
		else
			return 1;
	}
	
	function insertAttendanceFromCSV($present_employee_list){
		if(!is_array($present_employee_list) or !count($present_employee_list)) throw new \Exception("must pass array with present employee", 1);
		
		$att_query = "INSERT IGNORE into employee_attandance (employee_id,from_date,to_date,working_unit_count) VALUES ";
		
		foreach ($present_employee_list as $employee_id => $data) {	
				$emp_m = $this->add('xepan\hr\Model_Employee')
							->addCondition('id',$employee_id)
							->tryLoadAny();
				if(!$emp_m->loaded())
					continue;

				$emp_in_time = $emp_m['in_time'];
				$emp_out_time = $emp_m['out_time'];

				foreach ($data as $date => $value) {
					$working_type = $value['working_type_unit'];
					$unit_count = $value['unit_count'];
									
					$in_date_time = date("Y-m-d H:i:s", strtotime($date." ".$emp_in_time));
					$in_date_time_in_seconds = strtotime($in_date_time);

					$out_time = $emp_out_time;
					$working_time_in_sec = (strtotime($emp_m['out_time']) - strtotime($emp_m['in_time']));

					$out_date_time = $out_time;
					switch ($working_type) {
						case 'production_unit':
							$out_date_time = date("Y-m-d H:i:s", strtotime($date." ".$out_time));
							break;
						case 'monthly':
							$total_working_time_in_sec = $working_time_in_sec * $unit_count?:1;
							$new_time = $total_working_time_in_sec + $in_date_time_in_seconds;
							$out_date_time = date("Y-m-d H:i:s",$new_time);
							break;
						case 'hourly':
							$new_time = $in_date_time_in_seconds + ($unit_count * 60 * 60);
							// echo "In date Time = ".$in_date_time." unit count".$unit_count;
							$out_date_time = date("Y-m-d H:i:s",$new_time);
							break;
					}

					$emp_att_m = $this->add('xepan\hr\Model_Employee_Attandance');
					
					$att_query .= "('".$emp_m->id."', '".$in_date_time."','". $out_date_time."', '".$unit_count."'),";
				}
		}

		$att_query = trim($att_query,",");
		$att_query .= ";";
		$this->app->db->dsql()->expr($att_query)->execute();

	}
}