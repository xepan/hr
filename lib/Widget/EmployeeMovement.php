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
			if($g->model['first_in']== null)
				$g->current_row_html['in_at'] = 'Not In';
			else	
				$g->current_row_html['in_at'] = date('h:i A', strtotime($g->model['first_in']));
			
			if($g->model['is_late'] || $g->model['first_in']== null){
				$g->current_row_html['icon-class'] = 'fa-thumbs-o-down red-bg';
				$g->current_row_html['text-class'] = 'value red';
			}
			else{
				$g->current_row_html['icon-class'] = 'fa-thumbs-o-up green-bg';
				$g->current_row_html['text-class'] = 'value green';
			}
		});

		$this->grid->add('xepan\base\Controller_Avatar',['name_field'=>'name','image_field'=>'image_id','extra_classes'=>'profile-img center-block','options'=>['size'=>50,'display'=>'block','margin'=>'auto'],'float'=>null,'model'=>$this->model]);

		return parent::recursiveRender();
	}
}