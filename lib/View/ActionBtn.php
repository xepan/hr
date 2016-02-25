<?php


namespace xepan\hr;


class View_ActionBtn extends \CompleteLister{
	public $actions=[];

	function init(){
		parent::init();
		// throw new \Exception(print_r($this->actions,true), 1);
		
		$this->SetSource($this->actions);
	}
	function defaultTemplate(){
		return ['view/action-btn'];
	}
}