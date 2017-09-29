<?php

namespace xepan\hr;

class page_reimbursement extends \xepan\base\Page {
	public $title = "Reimbursement Management";
	
	function page_index(){

		$crud = $this->add('xepan\hr\CRUD');

		$model = $this->add('xepan\hr\Model_Reimbursement');
		$model->setOrder('created_at','desc');
		$crud->setModel($model,
			['employee_id','name'],
			['employee','name','created_at','amount','attachments_count']
			);
		$crud->add('xepan\base\Controller_MultiDelete');

		$crud->grid->addSno();
		$crud->grid->addColumn('expanderPlus','Detail');
		$crud->grid->addPaginator(50);
		$crud->grid->addQuickSearch(['name','employee']);
		$crud->grid->removeColumn('attachments_count');
		// $crud->grid->removeAttachment();
	}

	function page_Detail(){
		$reimbursement_m = $this->add('xepan\hr\Model_Reimbursement');
		$reimbursement_m->load($this->app->stickyGET('document_id'));

		$reimbursement_detail_m = $reimbursement_m->ref('Details');

		$crud = $this->add('xepan\hr\CRUD');
		$form = $crud->form;
		$form->add('xepan\base\Controller_FLC')
					->addContentSpot()
					->makePanelsCoppalsible()
					->layout([
						'name'=>'c1~4',
						'date'=>'c2~4',
						'amount'=>'c3~4',
						'narration'=>'c5~12'
					]);

		$crud->setModel($reimbursement_detail_m,
						['name','date','narration','amount'],
						['name','date','narration','amount']
					   );
		$crud->grid->addSno();
		$crud->grid->removeColumn('action');
		$crud->grid->removeAttachment();
	}

}