<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_hr';

	function setup_admin(){

		$this->routePages('xepan_hr');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
		->setBaseURL('../vendor/xepan/hr/');


		if($this->app->auth->isLoggedIn()){

            $m = $this->app->top_menu->addMenu('HR');
            $m->addItem(['Department','icon'=>'fa fa-sliders'],$this->app->url('xepan_hr_department',['status'=>'Active']));
            $m->addItem(['Post','icon'=>'fa fa-sitemap'],$this->app->url('xepan_hr_post',['status'=>'Active']));
            $m->addItem(['Employee','icon'=>'fa fa-male'],$this->app->url('xepan_hr_employee',['status'=>'Active']));
            $m->addItem(['Employee Movement','icon'=>'fa fa-eye'],'xepan_hr_employeemovement');
            $m->addItem(['User','icon'=>'fa fa-user'],$this->app->url('xepan_hr_user',['status'=>'Active']));
            $m->addItem(['Affiliate','icon'=>'fa fa-user'],$this->app->url('xepan_hr_affiliate',['status'=>'Active']));
            $m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
            
    		$this->app->employee = $this->recall(
                            $this->app->epan->id.'_employee',
                            $this->memorize(
                                $this->app->epan->id.'_employee',
                                $this->add('xepan\hr\Model_Employee')->tryLoadBy('user_id',$this->app->auth->model->id)
                            )
                        );

            if(!$this->app->employee->loaded()){
                $this->createDefaultEmployee();
                $this->app->redirect('.');
                exit;
            }

            $this->app->layout->template->trySet('department',$this->app->employee['department']);
            $post=$this->app->employee->ref('post_id');
            $this->app->layout->template->trySet('post',$post['name']);
            $this->app->layout->template->trySet('first_name',$this->app->employee['first_name']);
            $this->app->layout->template->trySet('status',$this->app->employee['status']);
            
            if($user_loggedin = $this->app->recall('user_loggedin',false)){
                $this->app->forget('user_loggedin');
                $this->api->employee->afterLoginCheck();
            }
            // $this->app->layout->add('xepan\hr\View_Notification',null,'notification_view');

            $this->app->layout->setModel($this->app->employee);
            $this->app->layout->add('xepan\base\Controller_Avatar');
            $this->app->addHook('user_loggedout',[$this->app->employee,'logoutHook']);
            
            $this->app->status_icon["xepan\hr\Model_Department"] = ['All'=>' fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\hr\Model_Post"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\hr\Model_Employee"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\base\Model_User"] = ['All'=>'fa fa-globe','Active'=>"fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
        }

        $search_department = $this->add('xepan\hr\Model_Department');
        $this->app->addHook('quick_searched',[$search_department,'quickSearch']);

        return $this;
	}

    function setup_frontend(){
        $this->routePages('xepan_hr');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('./vendor/xepan/hr/');
        $this->app->employee = $this->add('xepan\hr\Model_Employee');
        return $this;
    }



	function resetDB(){
        // Clear DB
        
        if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
        if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;

        $this->app->epan=$this->app->old_epan;
        $truncate_models = ['ACL','Activity','Employee','Post','Department'];
        foreach ($truncate_models as $t) {
            $m=$this->add('xepan\hr\Model_'.$t);
            foreach ($m as $mt) {
                $mt->delete();
            }
        }
        
        $this->app->epan=$this->app->new_epan;

        $this->createDefaultEmployee($dept,$post);

        // Do other tasks needed
        // Like empting any folder etc
    }

    function createDefaultEmployee(){

        // Create default Company Department
        $dept = $this->add('xepan\hr\Model_Department')
                    ->set('is_system',true)
                    ->set('name','Company')
                    ->set('production_level',1)
                    ->save();

        $this->app->db->dsql()->table('department')
                            ->set('production_level',0)
                            ->where('document_id',$dept->id)
                            ->execute();

        // Create default CEO/Owner Post
        $post = $this->add('xepan\hr\Model_Post')
                    ->set('name','CEO')
                    ->set('department_id',$dept->id)
                    ->save();
        $user = $this->add('xepan\base\Model_User_SuperUser')
                    ->addCondition('epan_id',$this->app->epan->id)
                    ->loadAny();

        // Create One Default Employee as CEO/Owner
        $emp = $this->add('xepan\hr\Model_Employee');
                $emp->set('type','Employee')
                ->set('first_name','Super')
                ->set('last_name','User')
                ->set('department_id',$dept->id)
                ->set('post_id',$post->id)
                ->set('user_id',$user->id)
                ->save();
    }
}
