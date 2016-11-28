<?php

namespace xepan\hr;

class page_deduction extends \xepan\base\Page{
	public $title = "Deduction Management";

	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD',null,null,['view/deduction']);

		$model = $this->add('xepan\hr\Model_Deduction');
		// $model->setOrder('created_at','desc');
		$crud->setModel($model);
		$crud->add('xepan\base\Controller_MultiDelete');
	}
}