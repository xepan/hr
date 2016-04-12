<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_employeemovement extends \Page {
	public $title='Employee Movement';
	public $breadcrumb=['Home'=>'index','Employee'=>'xepan_hr_employee','Movement'=>'#'];

	function init(){
		parent::init();
		$movement=$this->add('xepan\hr\Model_Employee_Movement');
		$c=$this->add('xepan\hr\CRUD',null,null,['view/employee/movement-grid']);
		$c->setModel($movement,['employee_id','direction','type','date','time'],['employee','direction','type','date','time']);

	}	
}
