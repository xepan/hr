<?php

/**
* description: xEpan ACL Controller, Realtion between document and contact
* Still confused if it should be in HR or Here. Must not mix multiple applications anyhow.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Controller_ACL extends \Abstract_Controller {

	function init(){
		parent::init();
		
		// 
		// filter model-data that user can see
		// manage add/edit/delete button on crud (self only or all)
		// check if trying to save (by hack URL) when not permitted on View_Document type view
		// Check action_view if not permitted block owner view
		// 
		// If Owner -> Model {
		// 	put -> can_view condition
		// 
		// }

	}
}
