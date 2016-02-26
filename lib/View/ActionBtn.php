<?php


namespace xepan\hr;


class View_ActionBtn extends \CompleteLister{
	public $actions=[];
	public $id;

	function init(){
		parent::init();
		// throw new \Exception(print_r($this->actions,true), 1);
		$temp_array=[];
		foreach ($this->actions as $value) {
			$temp_array[] = ['action'=>ucwords($value),'id'=>$this->id];
		}
		$first = array_pop($temp_array);

		$this->SetSource($temp_array);
		
		$this->template->set('action',$first['action']);
		$this->template->set('id',$first['id']);
		
		if(empty($temp_array)){
			$this->template->del('dropdown');
		}
	}
	function defaultTemplate(){
		return ['view/action-btn'];
	}
}