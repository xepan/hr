<?php
namespace xepan\hr;



class page_tests_setup extends \xepan\base\Page_Tester {
    public $title = 'HR Basic Tests';

    public $proper_responses=[
        'Test_checkCompany'=>['exists']
    ];

    function test_checkCompany(){
        $result =[];

        $result[] = $this->add('xepan\hr\Model_Department')
                ->tryLoadBy('name','company')
                ->loaded()?'exists':'company doesn\'t exists';

        return $result;
    }

}