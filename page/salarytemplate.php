<?php

namespace xepan\hr;

class page_salarytemplate extends \xepan\base\Page {
	
	function page_index(){

		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$temp_crud = $this->add('xepan\hr\CRUD',null,null,['page/config/salarytemplate']);
		
		$temp_crud->setModel($salary_template_m);

		$temp_crud->grid->addColumn('expander','Detail');
		$temp_crud->grid->addPaginator(5);
		$temp_crud->grid->addQuickSearch(['name']);
	}

	function page_Detail(){
		$salary_template_m = $this->add('xepan\hr\Model_SalaryTemplate');
		$salary_template_m->load($this->app->stickyGET('salary_template_id'));

		$salary_template_detail=$this->add('xepan\hr\Model_SalaryTemplateDetails');
		$salary_template_detail->acl = 'xepan\hr\Model_SalaryTemplate';
		$salary_template_detail->addCondition('salary_template_id',$salary_template_m->id);

		$crud=$this->add('xepan\hr\CRUD',null,null,['page/config/salarytemplatedetail']);
		$crud->setModel($salary_template_detail);
	}

}