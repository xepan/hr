<?php

namespace xepan\hr;
	
class page_trackgeolocationapi extends \Page{
	public $title = "Track Geolocation API";
	
	function init(){
		parent::init();
		file_put_contents('temp.txt', print_r($_POST,true));
		exit;
	}
}