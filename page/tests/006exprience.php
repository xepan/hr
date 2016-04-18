<?php

namespace xepan\hr;

class page_tests_006exprience extends \xepan\base\Page_Tester{
	public $title = 'HR Employee Experience';

    public $proper_responses=[ '-'=>'-'];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 

     function prepare_checkExperience(){
        $this->proper_responses['test_checkExperience']=[
            'name'=>'Xavoc',
            'department'=>'Development',
            'company_branch'=>'department',
            'salary'=>'0000',
            'designation'=>null,
            'duration'=>null,
            'employee'=>'Vijay Mali',
        ];
    }

    function test_checkExperience(){
          $tmp = $this->add('xepan\hr\Model_Employee')
                ->loadBy('user_id',$this->add('xepan\base\Model_User')->loadBy('username','xavoc@xavoc.com')->get('id'))
                ->ref('Experiences')->tryLoadAny();
  
        
        $result=[];
        foreach ($this->proper_responses['test_checkExperience'] as $field => $value) {
            $result[$field] = $tmp[$field];
        }

        return $result;   
    }

} 