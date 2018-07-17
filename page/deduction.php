<?php

namespace xepan\hr;

class page_deduction extends \xepan\base\Page{
	public $title = "Deduction Management";

	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD');
		if($crud->form){
			$form = $crud->form;
			$form->add('xepan\base\Controller_FLC')
				->showLables(true)
				->addContentSpot()
				->layout([
						'employee_id~Employee Name'=>'Deduction~c1~12',
						'created_at'=>'c2~6',
						'name~Reason'=>'c3~6',
						'amount'=>'c4~6',
						'received_amount'=>'c5~6',
						'narration'=>'c6~12',
						'FormButtons~&nbsp'=>'c7~4'
					]);
		}
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