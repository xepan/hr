<?php
namespace xepan\hr;
class Model_Qualificaton extends \xepan\base\Model_Table{
	public $table="qualificaton";
	function init(){
		parent::init();
		$this->hasOne('Employee');
		$this->addField('name')->caption('Qualificaton');
		$this->addField('qualificaton_level');
		$this->addField('percentage');

	}
}