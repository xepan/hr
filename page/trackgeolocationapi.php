<?php

namespace xepan\hr;
	
class page_trackgeolocationapi extends \Page{
	public $title = "Track Geolocation API";
	
	function init(){
		parent::init();

		

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

		$geodata = $this->getGeoData();
		

		$long = isset($geodata['longitude'])? $geodata['longitude']: false;
		$late = isset($geodata['latitude']) ? $geodata['latitude']: false;
		
		if($long && $late){
			$emp = $this->getEmployee();
			echo $emp['name'];
			if(!$geodata['time'] || ($geodata['time'] > strtotime($emp['last_geolocation_update'])) ){

				$emp['last_latitude'] = $late;
				$emp['last_longitude'] = $long;
				$emp['last_geolocation_update'] = $this->app->now;
				$emp['last_location'] = $geodata['street'].', '.$geodata['city']. ', ' . $geodata['state'];
				$emp->save();

			}
			exit;
		}

		exit;
	}

	function getGeoData(){
		$config = $this->app->getConfig('geolocationtrack',[]);
		switch (strtolower($config['location_mode'])) {
			case 'payload':
				$request_body = file_get_contents('php://input');
		        $data = json_decode($request_body,true);
				break;
			case 'get':
				$data=$_GET;
				break;
			case 'post':
				$data=$_POST;
				break;
			default:
				$data=[];
				break;
		}

		$senitised_data=$data;
		$senitised_data['longitude'] = $data[$config['longitude_field']];
		$senitised_data['latitude'] = $data[$config['latitude_field']];
		$senitised_data['time'] = $data[$config['time_field']];
	
		return $senitised_data;
	}

	function getEmployee(){
		$config = $this->app->getConfig('geolocationtrack',[]);
		switch (strtolower($config['employee_mode'])) {
			case 'payload':
				$request_body = file_get_contents('php://input');
		        $data = json_decode($request_body,true);
				break;
			case 'get':
				$data=$_GET;
				break;
			case 'post':
				$data=$_POST;
				break;
			default:
				$data=[];
				break;
		}
		$emp = $this->add('xepan\hr\Model_Employee')->tryLoad($data[$config['employee_field']]);
		return $emp;
	}
}