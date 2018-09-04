<?php

namespace xepan\hr;
	
class page_trackgeolocation extends \xepan\base\Page{
	public $title = "Track Geolocation";
	
	function page_index(){

		$long = isset($_POST['geodata']['coords']['longitude'])? $_POST['geodata']['coords']['longitude']: false;
		$late = isset($_POST['geodata']['coords']['latitude'])? $_POST['geodata']['coords']['latitude']: false;
		
		if($long && $late){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->load($this->app->employee->id);
			$emp['last_latitude'] = $late;
			$emp['last_longitude'] = $long;
			$emp['last_geolocation_update'] = $this->app->now;

			$emp->save();
			exit;
		}
		exit;
	}
}