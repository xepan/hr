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

class page_department extends \xepan\base\Page {
	public $title='Department';

	function init(){
		parent::init();
		
		$department=$this->add('xepan\hr\Model_Department');
		$department->add('xepan\base\Controller_TopBarStatusFilter');
		

		$department->setOrder('production_level','asc');
		// $crud=$this->add('xepan\hr\CRUD',null,null,['view/department/department-grid']);
		$crud=$this->add('xepan\hr\CRUD');
		$crud->grid->addPaginator(50);

		$crud->form->add('xepan\base\Controller_FLC')
			// ->addContentSpot()
			->layout([
				'name'=>'Department~c1~6',
				'is_outsourced'=>'c2~6',
				'production_level'=>'Production Department Details~c1~12~Usable if this department is in production chain system, other non-production department can have any number here',
				'simultaneous_no_process_allowed'=>'c2~12~If This department cannot handle more then this number of tasks, helps when you approve any sales-order, In case your all processes are running it stopes you approving more processes or make them in queue'
			]);
		
		
		if(!$crud->isEditing())
			$crud->grid->template->trySet('dept-url',$this->app->url('xepan_hr_structurechart'));

		$crud->setModel($department,['name','production_level','is_outsourced','simultaneous_no_process_allowed','posts_count','active_employee_count']);
		$crud->add('xepan\base\Controller_MultiDelete');

		if($crud->form->model['is_system']){
			$crud->form->getElement('production_level')->destroy();
		}

		$crud->grid->addHook('formatRow',function($g){
			if($g->model['is_system']) {
				$g->row_edit=false;
				$g->row_delete=false;
				$g->current_row_html['action']='';
			}else{
				$g->row_edit=true;
				$g->row_delete=true;
			}
			
		});

		$f=$crud->grid->addQuickSearch(['name']);

		$crud->grid->addFormatter('posts_count','template')->setTemplate('<a href="#pc" class="do-view-department-post">{$posts_count}</a>','posts_count');
		$crud->grid->addFormatter('active_employee_count','template')->setTemplate('<a href="#ec" class="do-view-department-employee">{$active_employee_count}</a>','active_employee_count');

		$crud->grid->js('click')->_selector('.do-view-department-post')->univ()->frameURL('Department Post',[$this->api->url('xepan_hr_post'),'department_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		$crud->grid->js('click')->_selector('.do-view-department-employee')->univ()->frameURL('Department Employee',[$this->api->url('xepan_hr_employee'),'department_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'),'status'=>'']);

		$crud->grid->addSno();
		$crud->noAttachment();

	}
}
