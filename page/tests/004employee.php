<?php

namespace xepan\hr;

class page_tests_004employee extends \xepan\base\Page_Tester{
	public $title = 'HR Employee';

    public $proper_responses=[ '-'=>'-'];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

     function prepare_checkEmployee(){
        $this->proper_responses['test_checkEmployee']=[
            'name'=>'Vijay Mali',
            'post'=>'Manager',
            'status'=>'Active',
            'qualification_count'=>0,
            'exprience_out'=>0
        ];
    }

    function test_checkEmployee(){
        $tmp = $this->add('xepan\hr\Model_Employee')
                ->tryLoadBy('name','Vijay Mali');
        
        $result=[];
        foreach ($this->proper_responses['test_checkEmployee'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;   
    }

} 