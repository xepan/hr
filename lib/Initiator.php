<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_hr';

	function init(){
		parent::init();
		$this->routePages('xepan_hr');
		
	}
}
