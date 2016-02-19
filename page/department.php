<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_department extends \Page {
	public $title='Department';

	function init(){
		parent::init();

		
		$department=$this->add('xepan\hr\Model_Department');
		// $department->tryLoadAny();

		$userlist = $this->add('CompleteLister',null,null,['page\department']);
		$userlist->setModel($department);

		$userlist->on('click','.post-link',$this->js()->univ()->location([$this->api->url('xepan_hr_contact_post'),'id'=>$this->js()->_selectorThis()->closest('tr')->data('id')]));


		// $form = $this->add('Form');
		// $form->setLayout(['page/department']);
		// $form->setModel($department,['name','production_level','status','posts']);
		$crud=$this->add('xepan\base\xCRUD',array('grid_class'=>'xepan\hr\Grid_Department','grid_options'=>array('grid_template'=>'grid/department-grid')));
		$crud->setModel($department);
		$crud->grid->addQuickSearch(['name']);
		// $crud->grid->addPaginator(2);
		// $form->onSubmit(function($f){
		// 	// return $f->displayError('first_name','HELLO');
		// 	$f->save();
		// 	return $f->js()->reload();
		// });
		
	}
}
