<?php

namespace xepan\hr;

class page_tests_001company extends \xepan\base\Page_Tester{
	public $title = 'HR Company Department';

    public $proper_responses=['-'=>'-' ];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

    function prepare_checkCompany(){
        $this->proper_responses['test_checkCompany']=[
            'name'=>'Company',
            'production_level'=>1,
        ];
    }

    function test_checkCompany(){
        $tmp = $this->add('xepan\hr\Model_Department')
                ->tryLoadBy('name','Company');
        
        $result=[];
        foreach ($this->proper_responses['test_checkCompany'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;
    }

} 