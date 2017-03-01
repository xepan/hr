<?php

namespace xepan\hr;

class Model_ReportExecutor extends \xepan\base\Model_Table{
	public $table='report_executor';
	
	public $status = ['Active','InActive'];
	public $actions = [
						'Active'=>['view','edit','delete','deactivate'],
						'InActive'=>['view','edit','delete','activate']
					  ];

	function init(){
		parent::init();
	}
}