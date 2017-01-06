<?php

namespace xepan\hr;

class page_reimbursement extends \xepan\base\Page {
	public $title = "Reimbursement Management";
	
	function page_index(){

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/reimbursement']);

		$model = $this->add('xepan\hr\Model_Reimbursement');
		$model->setOrder('created_at','desc');
		$crud->setModel($model);
		$crud->add('xepan\base\Controller_MultiDelete');

		$crud->grid->addColumn('expander','Detail');
		$crud->grid->addPaginator(5);
		$crud->grid->addQuickSearch(['name']);
	}

	function page_Detail(){
		$reimbursement_m = $this->add('xepan\hr\Model_Reimbursement');
		$reimbursement_m->load($this->app->stickyGET('document_id'));

		$reimbursement_detail_m = $reimbursement_m->ref('Details');

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/reimbursement-detail']);
		$crud->setModel($reimbursement_detail_m,
						['name','date','narration','amount'],
						['name','date','narration','amount']
					   );
	}

}