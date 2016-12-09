<?php
namespace xepan\hr;

class page_salarysheetedit extends \xepan\base\Page{
	public $title = "Salary Sheet";
	public $TotalWorkDays = 0;
	function init(){
		parent::init();
		
		$salary_sheet_id = $this->api->stickyGET('sheet_id');
		$model_sheet = $this->add('xepan\hr\Model_SalaryAbstract')->load($salary_sheet_id);

		$month = $model_sheet['month'];
		$year = $model_sheet['year'];

		// total work days
		$this->TotalWorkDays  = $model_sheet->getTotalWorkingDays($month,$year);

		$active_employee = $this->add('xepan\hr\Model_Employee')->addCondition('status','Active');

		$this->add('View')->setElement('h1')->set("Total Working Day Of ".$month." - ".$year." = ".$this->TotalWorkDays);

		$form = $this->add('Form');
		$all_salary = $this->add('xepan\hr\Model_Salary')->getRows();

		$system_calculated_factor = ['PaidLeaves','UnPaidLeaves','Absents','PaidDays'];
		
		// $salary_field_id_array = [];

		foreach ($active_employee as $employee) {

			// Get Employee Applied Salary
			$employee_applied_salary = $employee->getApplySalary();

			$view = $form->add('View')->setAttr('data-employee_id',$employee->id)->addClass('xepan-row-salarysheet-'.$employee->id);

			$cols = $view->add('Columns')->addClass('row panel panel-info');
			$col1 = $cols->addColumn(2)->addClass('col-md-2');
			$col1->addField('line','f_employee_name_'.$employee->id,'name')->set($employee['name']);
			$result = $employee->getSalarySlip($month,$year,$salary_sheet_id,$this->TotalWorkDays);

			//for pre defined system calculated Factor
			foreach ($system_calculated_factor as $name) {
				$value = 0;
				if(isset($result['calculated'][$name]))
					$value = $result['calculated'][$name];

				$new_col = $cols->addColumn(2)->addClass('col-md-2');
				$field = $new_col->addField('Number',"f_".$name."_".$employee->id, $name);
				$field->set($value);
				$field->addClass($name."_".$employee->id);
				$field->setAttr('data-employee-salary',$name);
				// $salary_field_id_array[$name] = $field->name;
			}
			//for all company salary
			foreach ($all_salary as $key => $salary) {
				$value = 0;
				if(isset($result['loaded'][$salary['name']]))
					$value = $result['loaded'][$salary['name']]?:0;
				elseif(isset($result['calculated'][$salary['name']]))
					$value = $result['calculated'][$salary['name']]?:0;
				else
					$value = 0;

				$new_col = $cols->addColumn(2)->addClass('col-md-2 ');
				$field_name  = "f_".$salary['name']."_".$employee->id;

				$applied_expression = 0;
				$add_deduction = "";
				if(isset($employee_applied_salary[$salary['id']])){
					$applied_expression = $employee_applied_salary[ $salary['id'] ]['expression'];
					// $add_deduction = $employee_applied_salary[$salary['id']]['add_deduction'];
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
			$field->set($result['calculated']['NetAmount']);
			$field->addClass('NetAmount'."_".$employee->id);
		}

		$form->addSubmit('Generate');
		if($form->isSubmitted()){
			try{
				foreach ($active_employee as $employee) {
					$salary_amount = [];
					foreach ($all_salary as $key => $salary) {
						$field = "f_".$salary['name']."_".$employee->id;
						$salary_amount[$salary['id']] = $form[$field];
					}

					$model_sheet->addEmployeeRow($employee->id,null,$salary_amount);
				}
			}catch(\Exception $e){
				throw $e;
				
				$form->js()->univ()->errorMessage('saving error ')->execute();
			}

			$form->js()->univ()->successMessage('saved successfully')->execute();
		}

		// js change on field
		$this->js(true)->_load('salarysheetcalculation');
		$form->js('change')->_selector('.do-change-salarysheet-factor')->univ()->doSalarySheetCalculation($this->js(true)->_selectorThis(),$this->js(true)->_selectorThis()->data('employee_id'));
	}
}