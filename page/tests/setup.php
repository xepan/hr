<?php
namespace xepan\hr;



class page_tests_setup extends \xepan\base\Page_Tester {
    public $title = 'HR Basic Tests';

    public $proper_responses=[];

    function init(){
        $this->add('xepan\hr\page_tests_init');
        parent::init();
    } 
   
}