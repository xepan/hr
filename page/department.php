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
		$department->add('xepan\hr\Controller_SideBarStatusFilter');
		
		if($status = $this->app->stickyGET('status'))
			$department->addCondition('status',$status);


		$department->setOrder('production_level','asc');
		$crud=$this->add('xepan\hr\CRUD',null,null,['view/department/department-grid']);
		$crud->grid->addPaginator(50);

		$crud->setModel($department);

		if($crud->form->model['is_system']){
			$crud->form->getElement('production_level')->destroy();
		}

		$crud->grid->addHook('formatRow',function($g){
			if($g->model['is_system']) {
				$g->current_row_html['edit']  = '<span class="fa-stack table-link"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>';
				$g->current_row_html['delete']= '<span class="table-link fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>';
				$g->current_row_html['action']='';
			}
			
		});

		$f=$crud->grid->addQuickSearch(['name']);
	}
}
