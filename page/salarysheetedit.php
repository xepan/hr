<?php
namespace xepan\hr;

class page_salarysheetedit extends \xepan\base\Page{
	public $title = "Salary Sheet";

	function init(){
		parent::init();
		
		$salary_sheet_id = $this->api->stickyGET('sheet_id');
		$model_sheet = $this->add('xepan\hr\Model_SalaryAbstract')->load($salary_sheet_id);

		$month = $model_sheet['month'];
		$year = $model_sheet['year'];

		$active_employee = $this->add('xepan\hr\Model_Employee')->addCondition('status','Active');

		$form = $this->add('Form');

		foreach ($active_employee as $employee) {
			$cols = $form->add('Columns')->addClass('row');
			$col1 = $cols->addColumn(2)->addClass('col-md-2');
			$col2 = $cols->addColumn(2)->addClass('col-md-2');
			$col1->addField('line','name_'.$employee->id,"")->set($employee['name']);
			$result= $employee->getSalarySlip($month,$year,$salary_sheet_id);
			// var_dump($result);			
			$col2->add('View')->setHtml(print_r($result,true));
			

		}
	}
}