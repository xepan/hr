<?php

namespace xepan\hr;

class page_dashboard extends \xepan\base\Page{
	public $title = 'Dashboard';

	function init(){
		parent::init();


		$employee = $this->add('xepan\hr\Model_Employee_Active');
		$total_employees = $employee->count()->getOne();
		
		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addExpression('present_today')->set(function($m,$q){
			return $m->refSQL('Attendances')
					->addCondition('employee_id',$q->getField('id'))
					->addCondition('from_date','>=',$this->app->today)->count();
		})->type('boolean');

		$employee->addCondition('present_today',true);

		$present_employees = $employee->count()->getOne();

		// engagement-percentage
     	$this->add('xepan\base\View_Chart',null,'Charts')
     		->setData(['columns'=> [['present', ($present_employees/$total_employees*100)]],
				        'type'=>'gauge'
				    	])
     		->setTitle('Work Force Available')
     		->addClass('col-md-4')
     		->setOption('color',['pattern'=>['#FF0000', '#F97600', '#F6C600', '#60B044'],'threshold'=>['values'=>[30, 60, 90, 100]]])
     		;

	    // =======  Avg Working Hours ===========
		$attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		// $attendances->join('employee.contact_id','employee_id')->addField('status');
		$attendances->addExpression('avg_work_hours')->set($attendances->dsql()->expr('AVG([0])',[$attendances->getElement('working_hours')]));
		$attendances->_dsql()->group('employee_id');
		// $attendances->addCondition('status','Active');
     	
     	$this->add('xepan\base\View_Chart',null,'Charts')
	    		->setType('bar')
	    		->setModel($attendances,'employee',['avg_work_hours'])
	    		// ->setData($data)
	    		->addClass('col-md-12')
	    		->rotateAxis()
	    		->setTitle('Employee Avg Work Hour')
	    		;

	    // =======  Late Coming or Extra time
	    $attendances = $this->add('xepan\hr\Model_Employee_Attandance');
		// $attendances->join('employee.contact_id','employee_id')->addField('status');
		$attendances->addExpression('avg_late')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('late_coming')]));
		$attendances->addExpression('avg_extra_work')->set($attendances->dsql()->expr('AVG([0])/60',[$attendances->getElement('extra_work')]));
		$attendances->_dsql()->group('employee_id');
		// $attendances->addCondition('status','Active');
     	
     	$this->add('xepan\base\View_Chart',null,'Charts')
	    		->setType('bar')
	    		->setModel($attendances,'employee',['avg_late','avg_extra_work'])
	    		// ->setData($data)
	    		->addClass('col-md-12')
	    		->rotateAxis()
	    		->setTitle('Employee Avg Late Coming & Extra Work')
	    		;

	    return;

		$employee_movement = $this->add('xepan\hr\Model_Employee');

		$count = 0;
		foreach ($employee_movement as $emp){
			$mov = $this->add('xepan\hr\Model_Employee_Movement');
			$mov->addCondition('employee_id',$emp->id);
			$mov->addCondition('movement_at','>=',$this->app->today);
			$mov->setOrder('movement_at','desc');
			$mov->tryLoadAny();

			if($mov['direction'] == 'Out' || !$mov->loaded())
				$count ++;
		}

		$this->template->trySet('out_employees',$count);

		$total_employee = $employee_movement->count()->getOne();
		$this->template->trySet('in_employees',abs($count-$total_employee));

	   	
	   	// engagement-percentage
     	$this->add('xepan\base\View_Chart',null)
     		->setData(['columns'=> [['data', 90,5]],
				        'type'=>'gauge'
				    	])
     		->setTitle('Current Engage');

     	$data=  ["columns"=> [
            // ['percentage', 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
            ['Score', 10, 30, 70, 90, 70, 75]
        ]];
		$this->add('xepan\base\View_Chart',null)
	    		->setType('line')
	    		// ->setModel($model,'Date',['lead_count','score_sum'])
	    		->setData($data)
	    		;

	   	// Employee Average working hour
        $data = ['columns'=> [
        			['x'=>'rakesh','gowrav'],
		            ['AVG Working Hour', 6, 8, 7, 5, 8, 10],
		            ['AVG Office Hour', 8, 9, 10, 8, 12, 13]
		        ],
		        'type'=> 'bar'];

        $this->add('xepan\base\View_Chart')
        		->setXAxis("x","x")
	    		->setType('bar')
	    		// ->setModel($model,'Date',['lead_count','score_sum'])
	    		->setData($data)
	    		->setTitle('Employee Working Hours')
	    		;

		// Employee In/Out List
        $data = ['columns'=> [
        			['x'=>'rakesh','gowrav'],
		            ['in', 6, 8, 7, 5, 8, 10],
		            ['out', 8, 9, 10, 8, 12, 13]
		        ],
		        'type'=> 'bar'];

        $this->add('xepan\base\View_Chart')
        		->setXAxis("x","x")
	    		->setType('bar')
	    		// ->setModel($model,'Date',['lead_count','score_sum'])
	    		->setData($data)
	    		->setTitle('Employee In/ Out graph')
	    		;

	    // Employee Efficiency graph
	    $data = ['columns'=> [
			            ['Responce Time', 6, 8, 7, 5, 8, 10],
			            ['Acceptance Time', 8, 9, 10, 8, 12, 13],
		        	],
		        	'type'=>"bar"
		        ];
	    $this->add('xepan\base\View_Chart')
     		->setType('bar')
     		// ->setModel($model,'name',['Email','Call','Meeting'])
     		->setData($data)
     		->setGroup(['Responce Time','Acceptance Time'])
     		->setTitle('Employee Efficiency')
     		->rotateAxis()
     		;
	}

	function defaultTemplate(){
		return ['page\hr\dashboard'];
	}
}