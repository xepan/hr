<?php
namespace xepan\hr;

class Grid_Department extends \xepan\base\Grid{
	public $grid_template='grid/department-grid';
	function init(){
		parent::init();
		$this->addButton('my');
	}
	function setModel($model){
		$model->getField('name')->caption('Department');
		$m=parent::setModel($model);
		$this->removeColumn('epan');
		return $m;
		
	}
	function defaultTemplate(){
		return[$this->grid_template];
	}
}