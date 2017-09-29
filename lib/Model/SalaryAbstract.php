<?php

namespace xepan\hr;

class Model_SalaryAbstract extends \xepan\base\Model_Table{
	public $table ="salary_abstract";

	function init(){
		parent::init();

		$this->hasOne('xepan\base\Contact','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->hasOne('xepan\base\Contact','updated_by_id')->defaultValue($this->app->employee->id)->system(true);

		$this->addField('created_at')->type('date')->defaultValue($this->app->now)->sortable(true);
		$this->addField('updated_at')->type('date')->defaultValue($this->app->now)->sortable(true);
		
		$this->addField('name');
		$this->addField('month')->setValueList(['1'=>"January",'2'=>"February",'3'=>"March",'4'=>"April",'5'=>"May",'6'=>"June",'7'=>"July",'8'=>"August",'9'=>"September",'10'=>"October",'11'=>"November",'12'=>"December"])->sortable(true);

		$current_year = $this->app->monthFirstDate();
		$year = [];
		for ($i=5; $i > 0; $i--) {
			$pre_year = date("Y",strtotime(date("Y-m-d", strtotime($current_year)) . " -".$i." YEAR" ));
			$year[$pre_year] = $pre_year;
		}

		for ($i=0; $i < 5; $i++) { 
			$next_year = date("Y",strtotime(date("Y-m-d", strtotime($current_year)) . " +".$i." YEAR" ));
			$year[$next_year] = $next_year;
		}
		
		$this->addField('year')->setValueList($year)->set(date('Y',strtotime($current_year)))->sortable(true);

		$this->addField('status')->defaultValue('Draft');
		$this->addField('type')->setValueList(['SalarySheet'=>'Salary Sheet','SalaryPayment'=>'Salary Payment'])->mandatory(true);
		$this->hasMany('xepan\hr\EmployeeRow','salary_abstract_id');

		$this->is([
			'name|required',
			'month|required',
			'year|required'
		]);

		$this->addHook('beforeDelete',[$this,'deleteEmpRow']);
	}

	function deleteEmpRow(){
		foreach($this->ref('xepan\hr\EmployeeRow') as $emp_row){
			$emp_row->delete();
		}
	}

	function addEmployeeRow($employee_id,$total_amount=null,$salary_detail=[],$calculated_field=[]){
		if(!$this->loaded()) throw new \Exception("model must loaded", 1);
		
		$row = $this->add('xepan\hr\Model_EmployeeRow');
		$row->addCondition('salary_abstract_id',$this->id);
		$row->addCondition('employee_id',$employee_id);
		$row->tryLoadAny();

		$row['total_amount'] = $total_amount;
		foreach ($calculated_field as $key=>$value) {
			$row[$key] = $value;
		}

		$row->save();

		if(count($salary_detail))
			$row->addSalaryDetail($salary_detail);

		return $row;
	}

	function getOfficialHolidays($month,$year,$TotalMonthDays){
		$oh_days = $this->add('xepan\hr\Model_OfficialHoliday');
		//	getWeekdays off from config in terms of array 0 => sunday 1= Monday
		$week_day_array = ['sunday'=>0,'monday'=>1,'tuesday'=>2,'wednesday'=>3,'thursday'=>4,'friday'=>5,'saturday'=>6];
		$ignore_array = [];
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

		if(!$week_day_model['monday']) $ignore_array[] = 1;
		if(!$week_day_model['tuesday']) $ignore_array[] = 2;
		if(!$week_day_model['wednesday']) $ignore_array[] = 3;
		if(!$week_day_model['thursday']) $ignore_array[] = 4;
		if(!$week_day_model['friday']) $ignore_array[] = 5;
		if(!$week_day_model['saturday']) $ignore_array[] = 6;
		if(!$week_day_model['sunday']) $ignore_array[] = 0;

		$wekklyOff = $this->countDays($month, $year, $ignore_array,$TotalMonthDays);

		// throw new \Exception($oh_days
		// 						->addCondition('month',$month)
		// 						->addCondition('year',$year)
		// 						->sum(
		// 								$this->dsql()->expr('IFNULL([0],0) + [1]',[$oh_days->getElement('month_holidays'),$wekklyOff]
		// 							)));
		
		$od =  $oh_days
					->addCondition('month',$month)
					->addCondition('year',$year)
					->sum(
						$this->dsql()->expr('IFNULL([0],0)',[$oh_days->getElement('month_holidays')])
						)
					->getOne()
					;

		return $od+$wekklyOff;
	}

	function getTotalWorkingDays($month,$year,$with_offcial_holiday=true){
		$TotalMonthDays = date('t',strtotime($year.'-'.$month.'-01'));
		if($with_offcial_holiday)
		return $TotalMonthDays;

 		$OfficialHolidays = $this->getOfficialHolidays($month,$year,$TotalMonthDays);
		return $TotalMonthDays - $OfficialHolidays;
	}

	function countDays($month, $year, $ignore=[],$TotalMonthDays=0){
	    $count = 0;
	    $counter = mktime(0, 0, 0, $month, 1, $year);
	    while (date("n", $counter) == $month) {
	        if (in_array(date("w", $counter), $ignore) == false) {
	            $count++;
	        }
	        $counter = strtotime("+1 day", $counter);
	    }
	   	
	   	if($TotalMonthDays)
	    	return $TotalMonthDays - $count;
	    return $count;
	}
}