<?php

/**
* description: xEpan HR Document Model where Few fields are redefined and 
* ACL also can be implmeneted implecit here if needed.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Model_Document extends \xepan\base\Model_Document{

	function init(){
		parent::init();

		$this->getElement('created_by_id')->destroy();
		$this->hasOne('xepan\hr\Employee','created_by_id');
		
	}
}
