<?php 

namespace xepan\hr;

class Widget_EmployeeMovement extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->grid = $this->add('xepan\hr\Grid',null,null,['view\employee\movement-mini']);
	}

	function recursiveRender(){
		$employee = $this->add('xepan\hr\Model_Widget_EmployeeMovement');
		
		$this->grid->setModel($employee,['name','first_in','last_out','is_late','in_color','out_color']);
		$this->grid->addPaginator(10);
		
		$this->grid->addHook('formatRow',function($g){
			if($g->model['is_late'] || $g->model['first_in']== null)
				$g->current_row_html['icon'] = 'fa fa-thumbs-o-down';
			else
				$g->current_row_html['icon'] = 'fa fa-thumbs-o-up';
		});

		return parent::recursiveRender();
	}
}