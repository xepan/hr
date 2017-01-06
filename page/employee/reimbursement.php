<?php

namespace xepan\hr;

class page_employee_reimbursement extends \xepan\hr\page_employee_myhr {
	public $title = "My Reimbursement";
	
	function page_index(){

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/reimbursement']);

		$model = $this->add('xepan\hr\Model_Reimbursement');
		$model->addCondition('employee_id',$this->app->employee->id);
		$model->setOrder('created_at','desc');

		$crud->setModel($model);

		$crud->grid->addColumn('expander','Detail');
		$crud->grid->addPaginator(5);
		$crud->grid->addQuickSearch(['name']);
	}

	function page_Detail(){
		$reimbursement_m = $this->add('xepan\hr\Model_Reimbursement');
		$reimbursement_m->load($this->app->stickyGET('document_id'));

		$reimbursement_detail_m = $reimbursement_m->ref('Details');

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/employee/reimbursement-detail']);
		$crud->setModel($reimbursement_detail_m,
						['name','date','narration','amount'],
						['name','date','narration','amount']
					   );
	}

}