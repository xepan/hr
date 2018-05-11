<?php

namespace xepan\hr;


class Form_Field_Post extends \xepan\base\Form_Field_DropDown {

	public $validate_values = false;
	public $id_field=null;
	public $title_field=null;
	public $include_status='Active'; // all, no condition
	public $class = 'xepan\hr\Model_Post';
	public $setCurrent=false;

	function init(){
		parent::init();
		$this->setEmptyText('Please Select');
	}

	function setIdField($id_field){
		$this->id_field = $id_field;
		return $this;
	}

	function setTitleField($title_field){
		$this->title_field = $title_field;
		return $this;
	}

	function includeAll(){
		$this->include_status=null;
		return $this;
	}

	function includeStatus($status){
		$this->include_status = $status;
		return $this;
	}

	function setCurrent(){
		$this->setCurrent=true;
		return $this;
	}

	function multiSelect(){
		$this->setAttr('multiple',true);
		return $this;
	}


	function recursiveRender(){
		$contact = $this->add($this->class);
		if($this->include_status) $contact->addCondition('status',$this->include_status);
		$this->setModel($contact,$this->id_field, $this->title_field);
		if($this->setCurrent) $this->set($this->app->employee['post_id']);
		parent::recursiveRender();
	}
}