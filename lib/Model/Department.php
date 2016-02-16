<?php
namespace xepan\hr;
class Department extends \Model_Table{
	public $table="department";
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan');
		$this->addField('name');
		$this->addField('production_level');
		$this->addField('status')->enum(['Active','DeActive']);

		$this->hasMany('xepan\hr\Post',null,null,'Posts');
	}
}