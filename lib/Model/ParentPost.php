<?php

namespace xepan\hr;

class Model_ParentPost extends \xepan\hr\Model_Post {
	var $table_alias= "pPost";
	public $title_field='dept_and_post';
	
	function init(){
		parent::init();

		$this->addExpression('dept_and_post')
			->set($this->dsql()->expr('CONCAT([0]," :: ",[1])',
				[
				$this->getElement('department'),$this->getElement('name')]))->sortable(true);


	}
}