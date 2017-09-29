<?php
namespace xepan\hr;

class page_salarysheet extends \xepan\base\Page{
	public $title = "Salary Sheet";

	function init(){
		parent::init();
		
		$model_salary = $this->add('xepan\hr\Model_SalarySheet');
		$model_salary->setOrder('month','desc');
		$model_salary->setOrder('year','desc');

		$crud = $this->add('xepan\hr\CRUD');
		$crud->form->add('xepan\base\Controller_FLC')
					->makePanelsCoppalsible()
					->layout([
						'name'=>'c1~3',
						'month'=>'c2~3',
						'year'=>'c3~3',
						'FormButtons~'=>'c4~3'
					]);

		// $crud = $this->add('xepan\hr\CRUD',null,null,['view/salarysheet/grid']);
		$crud->setModel($model_salary,
				['name','month','year'],
				['name','month','year','status']
			);

		$crud->grid->addHook('formatRow',function($g){
			$name = $g->model['name'];
			if(!$name){
				$name = $g->model['month']." - ".$g->model['year'];
			}
			$g->current_row_html['name'] = '<a href="?page=xepan_hr_salarysheetedit&sheet_id='.$g->model->id.'">'.$name.'</a>';
		});

		$crud->grid->addQuickSearch(['name','month','year']);
		$crud->grid->removeColumn('attachment_icon');
		$crud->grid->removeColumn('status');
		$crud->grid->addSno();
		$crud->grid->addPaginator($ipp=50);
	}
}