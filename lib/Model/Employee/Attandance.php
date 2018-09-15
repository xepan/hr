<?php

namespace xepan\hr;

/**
* 
*/
class Model_Employee_Attandance extends \xepan\base\Model_Table{
	public $table = "employee_attandance";
	public $acl = false;
	public $from_date=null;
	public $to_date=null;
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('from_date')->type('datetime');
		$this->addField('to_date')->type('datetime')->defaultValue(null);
		$this->addField('is_holiday')->type('boolean');
		$this->addField('working_unit_count')->defaultValue(1);
		$this->addField('late_coming')->type('int')->defaultValue(0);
		$this->addField('early_leave')->type('int')->defaultValue(0);
		$this->addField('total_work_in_mintues')->type('int')->defaultValue(0);
		$this->addField('total_movement_in')->type('int')->defaultValue(0);
		$this->addField('total_movement_out')->type('int')->defaultValue(0);

		$this->addExpression('fdate')->set('DATE(from_date)');
		$this->addExpression('tdate')->set('DATE(to_date)');
		$this->addExpression('ftime')->set('TIME(from_date)');
		$this->addExpression('ttime')->set('TIME(to_date)');

		$this->addExpression('status')->set(function($m,$q){
			return $q->expr('[0]',[$m->refSQL('employee_id')->fieldQuery('status')]);
		});

		$this->addExpression('official_day_start')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('in_time')
				]);
		});
		$this->addExpression('official_day_start_time')->set(function($m,$q){
			return $q->expr('TIME([0])',[$m->getElement('official_day_start')]);
		})->type('Time');

		$this->addExpression('official_day_end')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('out_time')
				]);
		});

		$this->addExpression('official_day_end_time')->set(function($m,$q){
			return $q->expr('TIME([0])',[$m->getElement('official_day_end')]);
		})->type('Time');
		

		// $this->addExpression('actual_day_ending')->set(function($m,$q){
		// 	return $q->expr('IFNULL([0],[1])',[
		// 								$q->getField('to_date'),
		// 								$m->getElement('official_day_end')
		// 							]
		// 					);
		// });

		// $this->addExpression('actual_day_start_time')->set('date_format(from_date,"%H:%i:%s")');
		// $this->addExpression('actual_day_end_time')->set(function($m,$q){
		// 	return $q->expr('date_format([0],"%H:%i:%s")',[
		// 								$m->getElement('actual_day_ending')
		// 								]
		// 					);

		// });

		// $this->addExpression('late_coming')->set(function($m,$q){
		// 	return $q->expr('TIMESTAMPDIFF(MINUTE,[0],[1])',[
		// 			$m->getElement('official_day_start'),
		// 			$q->getField('from_date'),
		// 		]);
		// });

		// $this->addExpression('late_coming')->set(function($m,$q){
		// 	return $q->expr(
		// 			"IF([is_holiday]='1','0',TIMESTAMPDIFF(MINUTE,[official_day_start],[from_date]))",
		// 				[
		// 					'official_day_start'=>$m->getElement('official_day_start'),
		// 					'from_date'=>$m->getElement('from_date'),
		// 					'is_holiday'=>$m->getElement('is_holiday')
		// 				]);
		// });

		$this->addExpression('extra_work')->set(function($m,$q){
			 $r = $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_per_day_extra_work_hours'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							// ->addCondition('is_holiday',false)
							->addCondition('early_leave','<',0)
							;
			return $q->expr('abs([0])',[$r->sum('early_leave')]);
		});
		
		$this->addExpression('extra_work_in_hour')->set(function($m,$q){
			return $q->expr('CONCAT(FLOOR([0]/60),":",MOD([0],60))',[$m->getElement('extra_work')]);
		});

		$this->addExpression('holidays_extra_work_minutes')->set(function($m,$q){
			 
			 $r = $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_extra_work_hours_in_holiday'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							->addCondition('is_holiday',true)
							;

			return $q->expr('[0]',[$r->sum('total_work_in_mintues')]);
		});

		$this->addExpression('holidays_extra_work_hours')->set(function($m,$q){
			return $q->expr('CONCAT(FLOOR(ABS([0])/60),":",MOD(ABS([0]),60))',[$m->getElement('holidays_extra_work_minutes')]);
		});

		$this->addExpression('working_hours')->set(function($m,$q){
			return $q->expr('TIMESTAMPDIFF(HOUR,[0],[1])',[
					$m->getElement('from_date'),
					$m->getElement('to_date'),
				]);
		});

		// late coming negative means early come
		// late coming positive means with late come

		// early_leave negative means late leave
		// early_leave positive means early leave

		$this->addExpression('total_in_time_login')->set(function($m,$q){
			return $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_intime_attan'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('late_coming','<=',0)
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							->count();
		});

		$this->addExpression('total_out_time_login')->set(function($m,$q){
			return $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_outtime_attan'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('late_coming','>',0)
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							->count();
		});

		$this->addExpression('total_logout_before_official_time')->set(function($m,$q){
			return $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_logouttime_before'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('early_leave','>',0)
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							->count();
		});

		$this->addExpression('total_logout_after_official_time')->set(function($m,$q){
			return $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_logouttime_after'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('early_leave','<=',0)
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							->count();
		});

		$this->addExpression('averge_late_minutes')->set(function($m,$q){

			$avg_hours = $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_latehours_attan'])
					->addCondition('employee_id',$q->getField('employee_id'))
					->addCondition('from_date','>=',$this->from_date)
					->addCondition('to_date','<',$this->api->nextDate($this->to_date))
					// ->sum('late_coming')
					;

			return $avg_hours->_dsql()->del('fields')->field(
							$q->expr('(SUM([0]) / ([1]) )',
									[
								$avg_hours->getElement('late_coming'),
								$avg_hours->count()
							]));		
		});

		// $this->addExpression('averge_late_hours')->set(function($m,$q){
		// 	return $q->expr('([0] / [1])',[$m->getElement('total_late_min'),$m->getElement('total_out_time_login')]);
		// });

		$this->addExpression('total_working_hours')->set(function($m,$q){
			$model = $m->add('xepan\hr\Model_Employee_Attandance',['table_alias'=>'emp_worknighours_attan'])
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('from_date','>=',$this->from_date)
							->addCondition('to_date','<',$this->api->nextDate($this->to_date))
							;

			return $q->expr('CONCAT(FLOOR([0]/60),":",MOD([0],60))',[$model->sum('total_work_in_mintues')]);
		});

		$this->addHook('beforeSave',$this);
	}
	function beforeSave(){
		
		if(!$this->loaded()){
			$official_in_time = $this->ref('employee_id')->get('in_time');
			$intime_date_diff = $this->app->my_date_diff(
						date('Y-m-d H:i:s',strtotime($this['from_date'])),
						date('Y-m-d H:i:s',strtotime($this->app->today." ".$official_in_time))
						);

			$factor = 1;
			if(strtotime($this['from_date']) < strtotime($this->app->today." ".$official_in_time))
				$factor = -1;

			$this['late_coming'] = $factor * $intime_date_diff['minutes_total'];
		}

		$official_out_time = $this->ref('employee_id')->get('out_time');
		$outtime_date = $this->app->my_date_diff(
						date('Y-m-d H:i:s',strtotime($this->app->today." ".$official_out_time)),
						date('Y-m-d H:i:s',strtotime($this['to_date']))
						);
		
		$factor = 1;
		if(strtotime($this['to_date']) > strtotime($this->app->today." ".$official_out_time))
			$factor = -1;

		$this['early_leave'] = $factor * $outtime_date['minutes_total'];

		$worknig_hour_date = $this->app->my_date_diff(
						date('Y-m-d H:i:s',strtotime($this['from_date'])),
						date('Y-m-d H:i:s',strtotime($this['to_date']))
						);
	
		$this['total_work_in_mintues'] = $worknig_hour_date['minutes_total'];

	}

	function getOfficialHoliday($date){
		$data = [];

		$start_date = date('Y-m-01',strtotime($date));
		$last_date = date('Y-m-t',strtotime($date));

		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');
		$holiday_model->addCondition('from_date','>=',$start_date);
		$holiday_model->addCondition('to_date','<=',$last_date);

		foreach($holiday_model->getRows() as $m){
			$begin = strtotime($m['from_date']);
			$end = strtotime($m['to_date']);
			for($i=$begin; $i<=$end; $i+=86400){
 			   $data[date("Y-m-d", $i)] = date("Y-m-d", $i);
			}
		}

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

		$off_day = [];
		foreach ($week_day_model->data as $day => $value) {
			if(!$value) $off_day[$day] = $day;
		}

		$start = new \DateTime($start_date);
		$end = new \DateTime($last_date);

		while ($start <= $end){
		    if(in_array(strtolower($start->format('l')), $off_day)){
		       $data[$start->format('Y-m-d')] = $start->format('Y-m-d');
		    }
		    $start->modify('+1 day');
		}
		
		return $data;
	}

	function isHoliday($today){
		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');
		$holiday_model->addCondition('from_date','<=',$today);
		$holiday_model->addCondition('to_date','>=',$today);
		
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
		
		$att_query = "INSERT IGNORE into employee_attandance (employee_id,from_date,to_date,working_unit_count,is_holiday,total_work_in_mintues) VALUES ";
		$del_query = "DELETE FROM employee_attandance where ";

		$official_holiday = 0;

		foreach ($present_employee_list as $employee_id => $data) {	
				$emp_m = $this->add('xepan\hr\Model_Employee')
							->addCondition('id',$employee_id)
							->tryLoadAny();
				if(!$emp_m->loaded())
					continue;

				$emp_in_time = $emp_m['in_time'];
				$emp_out_time = $emp_m['out_time'];

				foreach ($data as $date => $value) {

					if(!$official_holiday) $official_holiday = $this->getOfficialHoliday($date);

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

					// $emp_att_m = $this->add('xepan\hr\Model_Employee_Attandance');
					$is_holiday = 0;
					$in_date = date("Y-m-d", strtotime($in_date_time));

					if(isset($official_holiday[$in_date])) $is_holiday = 1;

					$worknig_hour_date = $this->app->my_date_diff(
							date('Y-m-d H:i:s',strtotime($in_date_time)),
							date('Y-m-d H:i:s',strtotime($out_date_time))
						);
					$att_query .= "('".$emp_m->id."', '".$in_date_time."','". $out_date_time."', '".$unit_count."','".$is_holiday."','".$worknig_hour_date['minutes_total']."'),";
					
					$del_query .= '(employee_id = '.$emp_m->id.' and date(from_date)="'.$in_date.'") OR ';
				}
		}

		$del_query = trim($del_query,"OR ");
		$del_query .= ';';
		$this->app->db->dsql()->expr($del_query)->execute();
		
		$att_query = trim($att_query,",");
		$att_query .= ";";
		$this->app->db->dsql()->expr($att_query)->execute();

	}
}