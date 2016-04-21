<?php

namespace xepan\hr;

class page_tests_005qualification extends \xepan\base\Page_Tester{
	public $title = 'HR Employee Qualification';

    public $proper_responses=[ '-'=>'-'];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

     function prepare_checkQualification(){
        $this->proper_responses['test_checkQualification']=[
            'name'=>'Test',
            'qualificaton_level'=>'Post Graduation',
            'remarks'=>'123',
            'employee'=>'Vijay Mali',
        ];
    }

    function test_checkQualification(){
         $tmp = $this->add('xepan\hr\Model_Employee')
                ->loadBy('user_id',$this->add('xepan\base\Model_User')->loadBy('username','xavoc@xavoc.com')->get('id'))
                ->ref('Qualifications')->tryLoadAny();

        
        $result=[];
        foreach ($this->proper_responses['test_checkQualification'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;   
    }

} 