<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_hr';

	function init(){
		parent::init();
		
		$this->routePages('xepan_hr');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
		->setBaseURL('../vendor/xepan/hr/');


		if($this->app->is_admin){

			if($this->app->auth->isLoggedIn())
				$this->app->employee = $this->add('xepan\hr\Model_Employee')->loadBy('user_id',$this->app->auth->model->id);

			$m = $this->app->top_menu->addMenu('HR');
			$m->addItem(['Department','icon'=>'fa fa-sliders'],'xepan_hr_department');
			$m->addItem(['Post','icon'=>'fa fa-sitemap'],'xepan_hr_post');
			$m->addItem(['Employee','icon'=>'fa fa-user'],'xepan_hr_employee');
			$m->addItem(['Employee Movement','icon'=>'fa fa-edit'],'xepan_hr_employeemovement');
			$m->addItem(['User','icon'=>'fa fa-male'],'xepan_hr_user');
			$m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
			
			$this->app->layout->template->trySet('department',$this->app->employee['department']);
			$post=$this->app->employee->ref('post_id');
	        $this->app->layout->template->trySet('post',$post['name']);
	        $this->app->layout->template->trySet('first_name',$this->app->employee['first_name']);
	        $this->app->layout->template->trySet('status',$this->app->employee['status']);
	        
	        // $this->app->layout->add('xepan\hr\View_Notification',null,'notification_view');
	        // $this->app->layout->add('xepan\base\View_Message',null,'message_view');

		}else{
            $this->app->employee = $this->add('xepan\hr\Model_Employee')
                                    ->addCondition('user_id',$this->add('xepan\base\Model_User_SuperUser')->setLimit(1)->setOrder('id')->fieldQuery('id'))
                                    ->tryLoadAny()
                                    ;
        }
	}

	function generateInstaller(){
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
                    ->set('production_level',1)
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

        // Do other tasks needed
        // Like empting any folder etc
    }
}
