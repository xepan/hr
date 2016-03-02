<?php


namespace xepan\hr;


class View_ActionBtn extends \CompleteLister{
	public $actions=[];
	public $status= 'StatusHERE';
	public $id;

	function init(){
		parent::init();
		// throw new \Exception(print_r($this->actions,true), 1);
		$temp_array=[];
		foreach ($this->actions as $value) {
			$temp_array[] = ['action'=>ucwords($value),'id'=>$this->id];
		}

		$this->SetSource($temp_array);
		
		$this->template->set('status',$this->status);
		
		if(empty($temp_array)){
			$this->template->del('dropdown');
		}
	}
	function defaultTemplate(){
		return ['view/action-btn'];
	}
}