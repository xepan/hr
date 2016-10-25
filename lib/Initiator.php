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
            $m->addItem(['Dashboard','icon'=>'fa fa-dashboard'],$this->app->url('xepan_hr_dashboard'));
            $m->addItem(['Department','icon'=>'fa fa-sliders'],$this->app->url('xepan_hr_department',['status'=>'Active']));
            $m->addItem(['Post','icon'=>'fa fa-sitemap'],$this->app->url('xepan_hr_post',['status'=>'Active']));
            $m->addItem(['Employee','icon'=>'fa fa-male'],$this->app->url('xepan_hr_employee',['status'=>'Active']));
            $m->addItem(['Employee Attandance','icon'=>'fa fa-check-square-o'],'xepan_hr_employeeattandance');
            $m->addItem(['Employee Movement','icon'=>'fa fa-eye'],'xepan_hr_employeemovement');
            $m->addItem(['Leave Management','icon'=>'fa fa-eye'],'xepan_hr_leavemanagment');
            // $m->addItem(['Payroll','icon'=>'fa fa-money'],'xepan_hr_payroll');
            $m->addItem(['User','icon'=>'fa fa-user'],$this->app->url('xepan_hr_user',['status'=>'Active']));
            $m->addItem(['Affiliate','icon'=>'fa fa-user'],$this->app->url('xepan_hr_affiliate',['status'=>'Active']));
            $m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
            $m->addItem(['Configuration','icon'=>'fa fa-cog'],'xepan_hr_config');
            
            if(!($this->app->employee = $this->app->recall($this->app->epan->id.'_employee',false))){                
                $this->app->employee = $this->add('xepan\hr\Model_Employee')->tryLoadBy('user_id',$this->app->auth->model->id);
                $this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);
            }

            if(!isset($this->app->resetDB) && !$this->app->employee->loaded()){
                throw new \Exception('User is not Employee', 1);
                
                // $this->createDefaultEmployee();
                // $this->app->redirect('.');
                // exit;
            }
            $this->app->user_menu->addItem(['Activity','icon'=>'fa fa-cog'],'xepan_hr_activity');
            $this->app->user_menu->addItem(['My HR','icon'=>'fa fa-cog'],'xepan_hr_employee_hr');
            // $m = $this->app->side_menu->addItem('HR');

            $this->app->layout->template->trySet('department',$this->app->employee['department']);
            // $post=$this->app->employee->ref('post_id');
            $this->app->layout->template->trySet('post',$this->app->employee['post']);
            $this->app->layout->template->trySet('first_name',$this->app->employee['first_name']);
            $this->app->layout->template->trySet('status',$this->app->employee['status']);
            
            if($user_loggedin = $this->app->recall('user_loggedin',false)){
                $this->app->forget('user_loggedin');
                $this->api->employee->afterLoginCheck();
            }
            $this->app->layout->add('xepan\hr\View_Notification',null,'notification_view');

            $this->app->layout->setModel($this->app->employee);
            $this->app->layout->add('xepan\base\Controller_Avatar');
            $this->app->addHook('user_loggedout',[$this->app->employee,'logoutHook']);
            
            $this->app->status_icon["xepan\hr\Model_Department"] = ['All'=>' fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\hr\Model_Post"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\hr\Model_Employee"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\base\Model_User"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
            $this->app->status_icon["xepan\hr\Model_Affiliate"] = ['All'=>'fa fa-globe','Active'=>"fa fa-circle text-success",'InActive'=>'fa fa-circle text-danger'];
        }else{
            $this->app->employee = $this->add('xepan\hr\Model_Employee');
        }

        $search_department = $this->add('xepan\hr\Model_Department');
        $this->app->addHook('quick_searched',[$search_department,'quickSearch']);
        $this->app->addHook('communication_created',[$this->app->employee,'communicationCreatedNotify']);

        $my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
        $my_email->addExpression('post_email')->set(function($m,$q){
         return $q->getField('email_username');
        });

        $contact_email=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');
        $or = $contact_email->dsql()->orExpr();
        $i=0;
         foreach ($my_email as $email) {
             $or->where('mailbox','like',$email['post_email'].'%');
             $i++;
         }
         if($i == 0) $or->where('mailbox',-1);       
        
        
        $contact_email->addCondition($or);
        $contact_email->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
        $contact_count=$contact_email->count()->getOne();

        $all_email=$this->add('xepan\communication\Model_Communication_Email_Received');
        $or = $all_email->dsql()->orExpr();
        $i=0;
         foreach ($my_email as $email) {
             $or->where('mailbox','like',$email['post_email'].'%');
             $i++;
         }
         if($i == 0) $or->where('mailbox',-1);       
        
        
        $all_email->addCondition($or);
        $all_email->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
        $all_count=$all_email->count()->getOne();
        
        $this->app->js(true)->html($contact_count." / ". $all_count)->_selector('.contact-and-all-email-count a span.atk-swatch-');

        return $this;
    }

    function setup_frontend(){
        $this->routePages('xepan_hr');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('./vendor/xepan/hr/');
        $this->app->employee = $this->add('xepan\hr\Model_Employee');
        $this->app->addHook('communication_created',[$this->app->employee,'communicationCreatedNotify']);
        return $this;
    }



	function resetDB(){
        // Clear DB
        
        // if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
        // if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;

        // $this->app->epan=$this->app->old_epan;
        // $truncate_models = ['ACL','Activity','Employee','Post','Department','Affiliate'];
        // foreach ($truncate_models as $t) {
        //     $m=$this->add('xepan\hr\Model_'.$t);
        //     foreach ($m as $mt) {
        //         $mt->delete();
        //     }
        // }
        
        // $this->app->epan=$this->app->new_epan;

        $this->createDefaultEmployee();

        // Do other tasks needed
        // Like empting any folder etc
    }

    function createDefaultEmployee(){

        // Create default Company Department
        $dept = $this->add('xepan\hr\Model_Department')
                    ->addCondition('is_system',true)
                    ->addCondition('name','Company')
                    ->addCondition('production_level',1)
                    ->tryLoadAny()
                    ->save();

        $this->app->db->dsql()->table('department')
                            ->set('production_level',0)
                            ->where('document_id',$dept->id)
                            ->execute();

        // Create default CEO/Owner Post
        $post = $this->add('xepan\hr\Model_Post')
                    ->addCondition('name','CEO')
                    ->addCondition('department_id',$dept->id)
                    ->tryLoadAny()
                    ->save();

        $user = $this->add('xepan\base\Model_User_SuperUser')
                    // ->addCondition('epan_id',$this->app->epan->id)
                    ->loadAny();

        // Create One Default Employee as CEO/Owner
        $emp = $this->add('xepan\hr\Model_Employee');
                $emp->set('type','Employee')
                ->addCondition('first_name','Super')
                ->addCondition('last_name','User')
                ->addCondition('department_id',$dept->id)
                ->addCondition('post_id',$post->id)
                ->addCondition('user_id',$user->id)
                ->tryLoadAny()
                ->save();
    }
}
