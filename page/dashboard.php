<?php

namespace xepan\hr;

class page_dashboard extends \xepan\base\Page{
	public $title = 'Dashboard';

	function init(){
		parent::init();

		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->_dsql()->group('department_id');
		$employee->addExpression('count','count(*)');
		
		$employee->addExpression('post_count')->set(function($m,$q){
			$dept = $this->add('xepan\hr\Model_Department');
			$dept->addCondition('name',$m->getElement('department'));
			$dept->setLimit(1);
			return $dept->fieldQuery('posts_count');
		});

		$this->add('xepan\base\Grid',null,'grid',['view\dashboard\grid'])->setModel($employee);

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
     	$this->add('xepan\base\View_Chart',null,'engagement_percentage')
     		->setData(['columns'=> [['data', 90]],
				        'type'=>'gauge'
				    	])
     		->setTitle('Current Engage');

     	$data=  ["columns"=> [
            // ['percentage', 10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
            ['Score', 10, 30, 70, 90, 70, 75]
        ]];
		$this->add('xepan\base\View_Chart',null,'engagement_graph')
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
		return ['page\dashboard\dashboard'];
	}
}