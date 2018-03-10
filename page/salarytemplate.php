<?php

namespace xepan\hr;

class page_salarytemplate extends \xepan\base\Page {
	
	function page_index(){

		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$temp_crud = $this->add('xepan\hr\CRUD');
		
		$temp_crud->setModel($salary_template_m,['name']);

		$temp_crud->grid->addColumn('expanderPlus','Detail');
		$temp_crud->grid->addPaginator(25);
		$temp_crud->grid->addQuickSearch(['name']);
		// $temp_crud->grid->removeColumn('action');
		$temp_crud->grid->removeAttachment();
		$temp_crud->grid->addSno();
	}

	function page_Detail(){
		$salary_template_id = $this->app->stickyGET('salary_template_id');

		$salary_template_detail = $this->add('xepan\hr\Model_SalaryTemplateDetails');
		$salary_template_detail->acl = 'xepan\hr\Model_SalaryTemplate';
		$salary_template_detail->addCondition('salary_template_id',$salary_template_id);

		$crud = $this->add('xepan\hr\CRUD');
		$crud->setModel($salary_template_detail);
		$crud->grid->addSno();
		// $crud->grid->removeColumn('action');
		$crud->grid->removeAttachment();
		$crud->grid->addFormatter('amount','Wrap');
	}
}