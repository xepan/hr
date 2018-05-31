<?php

namespace xepan\hr;

class page_officialholiday extends \xepan\hr\page_configurationsidebar{
	public $title="Official Holidays";
	function init(){
		parent::init();

		$holiday_model = $this->add('xepan\hr\Model_OfficialHoliday');
		$holiday_model->setOrder('from_date','desc');

		$crud = $this->add('xepan\hr\CRUD');
		$crud->form->add('xepan\base\Controller_FLC')
			->layout([
				'name'=>'Holiday~c1~3',
				'from_date'=>'c2~3',
				'to_date'=>'c3~3',
				'type'=>'c4~3'
			]);

		$crud->setModel($holiday_model,['name','from_date','to_date','type','month','month_holidays']);
		
		$crud->grid->addQuickSearch(['name','type']);
		$crud->grid->addSno();
		$crud->grid->removeAttachment();
		// $crud->grid->removeColumn('action');
		$crud->grid->addPaginator($ipp=50);
	}
}