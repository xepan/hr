<?php

namespace xepan\hr;


class Form_Field_Employee extends \xepan\base\Form_Field_DropDown {

	public $id_field=null;
	public $title_field=null;
	public $include_status='Active'; // all, no condition
	public $contact_class = 'xepan\hr\Model_Employee';
	public $setCurrent=false;

	function init(){
		parent::init();
		$this->setEmptyText('Please Select');
	}

	function setType($type=null){
		if($type) $this->addCondition('type',$type);
		return $this;
	}

	function setContactType($contact_type){
		$this->contact_class = $contact_type;
		return $this;
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


	function recursiveRender(){
		$contact = $this->add($this->contact_class);
		if($this->include_status) $contact->addCondition('status',$this->include_status);
		$this->setModel($contact,$this->id_field, $this->title_field);
		if($this->setCurrent) $this->set($this->app->employee->id);
		parent::recursiveRender();
	}
}