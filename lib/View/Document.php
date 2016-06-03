<?php

/**
* description: View_Document is special View that helps to edit 
* any View in its own template by using same template as form layout
* It also helps in managing hasMany relations to be Viewed and Edit on same Level
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class View_Document extends \xepan\base\View_Document{

	function setModel($model,$view_fields=null,$form_fields=null){
		$m = parent::setModel($model,$view_fields,$form_fields);

		if(($m instanceof \xepan\base\Model_Table) && !@$this->pass_acl){
			$this->add('xepan\hr\Controller_ACL');
		}
		return $m;
	}

}
