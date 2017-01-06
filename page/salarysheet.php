<?php
namespace xepan\hr;

class page_salarysheet extends \xepan\base\Page{
	public $title = "Salary Sheet";

	function init(){
		parent::init();
		
		$model_salary = $this->add('xepan\hr\Model_SalarySheet');

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/salarysheet/grid']);
		$crud->setModel($model_salary);

		$crud->grid->addHook('formatRow',function($g){
			$name = $g->model['name'];
			if(!$name){
				$name = $g->model['month']." - ".$g->model['year'];
			}
			$g->current_row_html['name'] = '<a href="?page=xepan_hr_salarysheetedit&sheet_id='.$g->model->id.'">'.$name.'</a>';
		});

		if($crud->grid){
			$crud->grid->removeColumn('attachment_icon');
		}

	}
}