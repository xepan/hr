<?php

namespace xepan\hr;

class page_tests_init extends \AbstractController{
	function init(){
		parent::init();

		$this->app->xepan_app_initiators['xepan\hr']->resetDB();

		$dept = $this->add('xepan\hr\Model_Department')
                    ->set('is_system',false)
                    ->set('name','Development')
                    ->set('production_level',2)
                    ->save();

        $post = $this->add('xepan\hr\Model_Post')
                    ->set('name','Manager')
                    ->set('department_id',$dept->id)
                    ->set('status','Active')
                    ->save();

        $u = $this->add('xepan\base\Model_User_SuperUser')
                    ->addCondition('username','xavoc@xavoc.com')
                    ->tryLoadAny()
                    ->set('password','123')
                    ->saveAs('xepan\base\Model_User_SuperUser');

        $emp = $this->add('xepan\hr\Model_Employee')
                ->set('first_name','Vijay')
                ->set('last_name','Mali')
                ->set('department_id',$dept->id)
                ->set('post_id',$post->id)
                ->set('user_id',$u->id)
                ->save();

        $qua = $this->add('xepan\hr\Model_Qualification')
                ->set('employee_id',$emp->id)
                ->set('name','Test')
                ->set('qualificaton_level','Post Graduation')
                ->set('remarks','123')
                ->save();


        $exp = $this->add('xepan\hr\Model_Experience')
                ->set('employee_id',$emp->id)
                ->set('name','Xavoc')
                ->set('department','Development')
                ->set('company_branch','department')
                ->set('salary','0000')
                ->set('designation',null)
                ->set('duration',null)
                ->save();

    }
}