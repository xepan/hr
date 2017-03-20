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

		foreach ($present_employee_list as $employee_id => $data) {
			
			try{
				$this->api->db->beginTransaction();
				
				$emp_att_m = $this->add('xepan\hr\Model_Employee_Attandance');

				$emp_m = $this->add('xepan\hr\Model_Employee')
							->addCondition('id',$employee_id)
							->tryLoadAny();

				if(!$emp_m->loaded())
					continue;

				$emp_att_m['employee_id'] = $emp_m->id;

				foreach ($data as $date => $value) {
					$emp_att_m['from_date'] = $date;
				} 

				$emp_att_m->save();
				$emp_att_m->unload();

				$this->api->db->commit();
			}catch(\Exception $e){
				echo $e->getMessage()."<br/>";
				continue;
			}
		}
	}

	function insertAttendanceFromCSVForWeek($present_employee_list){
		
	}

}