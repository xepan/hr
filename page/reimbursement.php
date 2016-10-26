<?php

namespace xepan\hr;

class page_reimbursement extends \xepan\base\Page{
	public $title = "Reimbursement Management";

	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/reimbursement']);

		$model = $this->add('xepan\hr\Model_Reimbursement');
		$model->setOrder('created_at','desc');
		$crud->setModel($model);
		$crud->addRef('Details',[
									'view_class'=>"xepan\base\CRUD",
									'label'=>"Details",
									'fields'=>['name','date','narration','amount']
								]);

	}
}