<?php

namespace xepan\hr;

class page_deduction extends \xepan\base\Page{
	public $title = "Deduction Management";

	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD');
		$model = $this->add('xepan\hr\Model_Deduction');
		$model->setOrder('created_at','desc');
		$crud->setModel($model,
			['employee_id','created_at','name','amount','narration','received_amount','due_amount','created_by'],
			['employee','created_at','name','amount','narration','received_amount','due_amount','created_by']
		);

		$crud->grid->addSno();
		$crud->add('xepan\base\Controller_MultiDelete');
	}
}