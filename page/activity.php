<?php

namespace xepan\hr;

class page_activity extends \xepan\base\Page{
	public $title="Activities";
	function init(){
		parent::init();

		$this->add('xepan\base\View_Activity');
	}
}