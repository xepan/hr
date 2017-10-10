<?php

namespace xepan\hr;

class page_test extends \xepan\base\Page{
	public $title = "TEST PAGE";
	function init(){
		parent::init();

		$form= $this->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->layout([
					'employee'=>'Employee~c1~12',
					'c'=>'Address~c1~6',
					's'=>'c2~6'
			]);
		$form->addField('xepan\base\Contact','employee');
		$c_f = $form->addField('xepan\base\Country','c');//->includeAll();
		$s_f = $form->addField('xepan\base\State','s');
		$s_f->dependsOn($c_f);

		$form->addSubmit();

		if($form->isSubmitted()){
			$form->js()->univ()->successMessage(json_encode($form->get()))->execute();
		}

		$c =$this->add('xepan\hr\CRUD');
		$c->grid->addColumn('xepan\hr\Employee','actor',['actor_field'=>'created_by','allow_dig'=>true]);
		$c->setModel('xepan\hr\Department');

		if($c->isEditing('edit')){
			$c->form->getElement('created_by_id')->includeAll();
		}
		
    	// $c->grid->add('Order')->move('actor','first')->later();


	}
}