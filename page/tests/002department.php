<?php

namespace xepan\hr;

class page_tests_002department extends \xepan\base\Page_Tester{
	public $title = 'HR Department';

    public $proper_responses=['-'=>'-' ];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

    function prepare_checkDevelopment(){
        $this->proper_responses['test_checkDevelopment']=[
            'name'=>'Development',
            'production_level'=>2,
            'is_system'=>false,
        ];
    }

    function test_checkDevelopment(){
        $tmp = $this->add('xepan\hr\Model_Department')
                ->tryLoadBy('name','Development');
        
        $result=[];
        foreach ($this->proper_responses['test_checkDevelopment'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;
    }

} 