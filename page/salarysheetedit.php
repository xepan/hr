<?php
namespace xepan\hr;

class page_salarysheetedit extends \xepan\base\Page{
	public $title = "Salary Sheet";
	public $breadcrumb=['Home'=>'index','Salary Sheet'=>'xepan_hr_salarysheet','Detail'=>'#'];
	public $TotalWorkDays = 0;
	function init(){
		parent::init();

		ini_set('memory_limit', '-1');
		set_time_limit(0);
		
		$salary_sheet_id = $this->api->stickyGET('sheet_id');
		$model_sheet = $this->add('xepan\hr\Model_SalaryAbstract')->load($salary_sheet_id);

		$month = $model_sheet['month'];
		$year = $model_sheet['year'];

		// total work days
		$this->TotalWorkDays  = $model_sheet->getTotalWorkingDays($month,$year);

		$active_employee = $this->add('xepan\hr\Model_Employee');

		$last_date = date('Y-m-t',strtotime($year."-".$month."-01"));
		$start_date = date('Y-m-01',strtotime($year."-".$month."-01"));

		$active_employee->addExpression('attendane_count')->set(function($m,$q)use($start_date,$last_date){
			return $m->add('xepan\hr\Model_Employee_Attandance')
				->addCondition('employee_id',$m->getElement('id'))
				->addCondition('from_date','>=',$start_date)
				->addCondition('to_date','<=',$last_date)
				->count();
		});
		$active_employee->addCondition([['attendane_count','>',0],['status','Active']]);
		$this->add('View')->setElement('h1')
			->set("Total Working Day Of ".$month." - ".$year." = ".$this->TotalWorkDays);

		$form = $this->add('Form');
		$all_salary = $this->add('xepan\hr\Model_Salary')->getRows();
		
		foreach ($all_salary as $key=>$salary) {
			$all_salary[$key]['name'] = preg_replace('/\s+/', '',$salary['name']);
		}

		$all_salary_for_js = [];
		$all_salary_for_js[] = ['name'=>'Presents'];
		$all_salary_for_js[] = ['name'=>'PaidLeaves'];
		$all_salary_for_js[] = ['name'=>'UnPaidLeaves'];
		$all_salary_for_js[] = ['name'=>'Absents'];
		$all_salary_for_js[] = ['name'=>'OfficialHolidays'];
		$all_salary_for_js[] = ['name'=>'ExtraWorkingDays'];
		$all_salary_for_js[] = ['name'=>'ExtraWorkingHours'];
		$all_salary_for_js[] = ['name'=>'PaidLeavesOnHoliday'];
		$all_salary_for_js[] = ['name'=>'UnPaidLeavesOnHoliday'];
		$all_salary_for_js[] = ['name'=>'PaidDays'];

		$all_salary_for_js = array_merge($all_salary_for_js,$all_salary);

		$system_calculated_factor = ['presents'=>'Presents','paid_leaves'=>'PaidLeaves','unpaid_leaves'=>'UnPaidLeaves','absents'=>'Absents','OfficialHolidays'=>'OfficialHolidays','ExtraWorkingDays'=>'ExtraWorkingDays','ExtraWorkingHours'=>'ExtraWorkingHours','PaidLeavesOnHoliday'=>'PaidLeavesOnHoliday','UnPaidLeavesOnHoliday'=>'UnPaidLeavesOnHoliday','paiddays'=>'PaidDays'];

		foreach ($active_employee as $employee) {

			// echo $employee['name']."<br/>";
			// Get Employee Applied Salary
			$employee_applied_salary = $employee->getApplySalary();

			$view = $form->add('View')->setAttr('data-employee_id',$employee->id)->addClass('xepan-row-salarysheet-'.$employee->id);

			$cols = $view->add('Columns')->addClass('row panel panel-info');
			$error_section = $cols->addColumn(12);
			$col1 = $cols->addColumn(2)->addClass('col-md-2');
			$col1->addField('line','f_employee_name_'.$employee->id,'name')->set($employee['name']);
			$result = [];
			try{
				$result = $employee->getSalarySlip($month,$year,$salary_sheet_id,$this->TotalWorkDays);
			}catch(\Exception $e){
				$error_section->add('View')->addClass('alert alert-danger')->set($e->getMessage())->setStyle('margin-top','0px;');
			}

			$col1->add('View')->set("Working Mode : ".$employee['salary_payment_type']);

			//for pre defined system calculated Factor
			foreach ($system_calculated_factor as $key => $name) {
				// echo $name." = ";
				if(isset($result['loaded'][$name]) AND $result['loaded'][$name] > 0)
					$value = $result['loaded'][$name];
				elseif (isset($result['calculated'][$name]) AND $result['calculated'][$name] > 0) {
					$value = $result['calculated'][$name];
				}else
					$value = 0;

				// echo $value."<br/>";
				$new_col = $cols->addColumn(2)->addClass('col-md-2');
				$field = $new_col->addField('Number',"f_".$name."_".$employee->id, $name);
				$field->set($value);
				$field->addClass($name."_".$employee->id);
				$field->addClass("do-change-salarysheet-factor system-calculated-salary");
				$field->setAttr('data-employee-salary',$name);
				$field->setAttr('data-add_deduction',"dummy");
				$field->setAttr('data-employee_id',$employee->id);

				// $field->setAttr('data-xepan-salarysheet-expression',"");
				if($name === "PaidDays")
					$field->setAttr('data-xepan-salarysheet-expression','{Presents}+{PaidLeaves}+{OfficialHolidays}');
				// $salary_field_id_array[$name] = $field->name;
			}
			$cols->addColumn(12)->addClass('col-md-12')->add('HR');
			//for all company salary
			foreach ($all_salary as $key => $salary) {
				$value = 0;
				if(isset($result['loaded'][$salary['name']]) AND $result['loaded'][$salary['name']] > 0)
					$value = $result['loaded'][$salary['name']]?:0;
				elseif(isset($result['calculated'][$salary['name']]) AND $result['calculated'][$salary['name']])
					$value = $result['calculated'][$salary['name']]?:0;
				else
					$value = 0;
				
				// echo $employee['name']." salary= ".$salary['name']." value= ".$value."<br/>";

				$new_col = $cols->addColumn(2)->addClass('col-md-2 ');
				$field_name  = "f_".$salary['name']."_".$employee->id;

				$applied_expression = "";
				$add_deduction = "";
				if(isset($employee_applied_salary[$salary['id']])){
					$applied_expression = preg_replace('/\s+/', '', $employee_applied_salary[ $salary['id'] ]['expression'])?:"";
					$add_deduction = $employee_applied_salary[$salary['id']]['add_deduction'];
				}
				
				$new_col->addField('Number',$field_name,$salary['name'])
							->set($value)
							->addClass('do-change-salarysheet-factor')
							->addClass($salary['name']."_".$employee->id)
							->setAttr('data-xepan-salarysheet-expression',$applied_expression)
							->setAttr('data-add_deduction',$add_deduction)
							->setAttr('data-employee_id',$employee->id)
							->setAttr('data-employee-salary',$salary['name'])
							;
			}

			$new_col = $cols->addColumn(2)->addClass('col-md-2');
			$field_name  = "f_NetAmount_".$employee->id;
			$field = $new_col->addField('Number',$field_name,'Net Amount');
			$field->set(isset($result['loaded']['NetAmount'])?$result['loaded']['NetAmount']:$result['calculated']['NetAmount']);
			$field->addClass('NetAmount'."_".$employee->id);
		}

		// die();
		$form->addSubmit('Generate');
		if($form->isSubmitted()){
			try{

				foreach ($active_employee as $employee) {
					$salary_amount = [];
					
					$calculated_array = [
											'presents'=>$form['f_Presents_'.$employee->id],
											'paid_leaves'=>$form['f_PaidLeaves_'.$employee->id],
											'unpaid_leaves'=>$form['f_UnPaidLeaves_'.$employee->id],
											'absents'=>$form['f_Absents_'.$employee->id],
											'paiddays'=>$form['f_PaidDays_'.$employee->id],
											'officialholidays'=>$form['f_OfficialHolidays_'.$employee->id],
											'extraworkingdays'=>$form['f_ExtraWorkingDays_'.$employee->id],
											'extraworkinghours'=>$form['f_ExtraWorkingHours_'.$employee->id],
											'paidleavesonholiday'=>$form['f_PaidLeavesOnHoliday_'.$employee->id],
											'unpaidleavesonholiday'=>$form['f_UnPaidLeavesOnHoliday_'.$employee->id],
											'total_working_days'=>$this->TotalWorkDays
										];

					foreach ($all_salary as $key => $salary) {
						$field = "f_".$salary['name']."_".$employee->id;
						$salary_amount[$salary['id']] = $form[$field];
					}
					
					$model_sheet->addEmployeeRow($employee->id,$form['f_NetAmount_'.$employee->id],$salary_amount,$calculated_array);
				}
					
					$salary_sheet = $this->add('xepan\hr\Model_SalarySheet');

					if($model_sheet->loaded() && $model_sheet['status'] === "Approved")
						$salary_sheet->load($model_sheet->id)->approved();

			}catch(\Exception $e){
				throw $e;
				
				$form->js()->univ()->errorMessage('saving error ')->execute();
			}

			$form->js()->univ()->successMessage('saved successfully')->execute();
		}


		// js change on field
		$this->js(true)->_load('salarysheetcalculation');
		$form->js('change')->_selector('.do-change-salarysheet-factor')->univ()->doSalarySheetCalculation($this->js(true)->_selectorThis(),$this->js(true)->_selectorThis()->data('employee_id'),$this->TotalWorkDays,$all_salary_for_js);
	}
}