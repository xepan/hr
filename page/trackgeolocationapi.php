<?php

namespace xepan\hr;
	
class page_trackgeolocationapi extends \Page{
	public $title = "Track Geolocation API";
	
	function init(){
		parent::init();

		$request_body = file_get_contents('php://input');
        $data = json_decode($request_body,true);

    	// file_put_contents('temp.txt', print_r($data,true).print_r($_GET,true));
        /* 
        	Array
			(
			    [number] => null
			    [time] => 1536054877175
			    [longitude] => 73.7088336
			    [city] => Gordhan Vilas Rural
			    [state] => RJ
			    [accuracy] => 104.0999984741211
			    [altitude] => 0.0
			    [street] => Savina Main Rd
			    [speed] => 0.0
			    [imei] => 351665069837594
			    [neighborhood] => null
			    [bearing] => 0.0
			    [latitude] => 24.5483392
			    [fixed] => false
			)
			Array
			(
			    [page] => xepan_hr_trackgeolocationapi
			    [emp] => 123 //emp id
			)
        */

		$emp = $_GET['emp'];
		$emp = explode("-", $emp);
		
		$emp_id= $emp[0];
		$emp_hash = $emp[1];

		$long = isset($data['longitude'])? $data['longitude']: false;
		$late = isset($data['latitude']) ? $data['latitude']: false;
		
		if($long && $late){
			$emp = $this->add('xepan\hr\Model_Employee');
			$emp->load($emp_id);

			if($data['time'] > strtotime($emp['last_geolocation_update']) ){

				$emp['last_latitude'] = $late;
				$emp['last_longitude'] = $long;
				$emp['last_geolocation_update'] = $this->app->now;
				$emp['last_location'] = $data['street'].', '.$data['city']. ', ' . $data['state'];
				$emp->save();

			}
			exit;
		}


		exit;
	}
}