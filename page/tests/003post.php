<?php

namespace xepan\hr;

class page_tests_003post extends \xepan\base\Page_Tester{
	public $title = 'HR Post';

    public $proper_responses=['-'=>'-' ];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

    function prepare_checkPost(){
        $this->proper_responses['test_checkPost']=[
            'name'=>'Manager',
            'department'=>'Development',
            'status'=>'Active',
        ];
    }

    function test_checkPost(){
      $tmp = $this->add('xepan\hr\Model_Post')
                ->tryLoadBy('name','Manager');
        
        $result=[];
        foreach ($this->proper_responses['test_checkPost'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;   
    }

} 