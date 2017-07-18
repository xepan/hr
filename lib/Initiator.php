<?php

namespace xepan\hr;

class Initiator extends \Controller_Addon {
    
    public $addon_name = 'xepan_hr';

    function setup_admin(){
        $this->addAppFunctions();
        $this->routePages('xepan_hr');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('../vendor/xepan/hr/');


        if(!$this->app->isAjaxOutput() && !$this->app->getConfig('hidden_xepan_hr',false)){
            $this->app->side_menu->addItem(['Document','icon'=>' fa fa-folder','badge'=>["0",'swatch'=>' label label-primary pull-right']],'xepan_hr_document')->setAttr(['title'=>'Documents']);
        }

        if($this->app->auth->isLoggedIn()){

            if($_GET['keep_alive_signal']){
                echo "// keep-alive";
                $this->app->js()->execute();
            }
            $this->app->js(true)->univ()->setInterval($this->app->js()->univ()->ajaxec($this->api->url('.',['keep_alive_signal'=>true]))->_enclose(),120000);

            if(!$this->app->getConfig('hidden_xepan_hr',false)){
                $m = $this->app->top_menu->addMenu('HR');
                // $m->addItem(['Dashboard','icon'=>'fa fa-dashboard'],$this->app->url('xepan_hr_dashboard'));
                $m->addItem(['Department','icon'=>'fa fa-sliders'],$this->app->url('xepan_hr_department',['status'=>'Active']));
                $m->addItem(['Post','icon'=>'fa fa-sitemap'],$this->app->url('xepan_hr_post',['status'=>'Active']));
                $m->addItem(['Employee','icon'=>'fa fa-male'],$this->app->url('xepan_hr_employee',['status'=>'Active']));
                
                if(!$this->app->getConfig('base_hr_only',false)){   
                    $m->addItem(['Employee Attandance','icon'=>'fa fa-check-square-o'],'xepan_hr_attandance');
                    $m->addItem(['Employee Movement','icon'=>'fa fa-eye'],'xepan_hr_employeemovement');
                    $m->addItem(['Leave Management','icon'=>'fa fa-eye'],'xepan_hr_leavemanagment');
                    $m->addItem(['Reimbursement Management','icon'=>'fa fa-money'],'xepan_hr_reimbursement');
                    $m->addItem(['Deduction Management','icon'=>'fa fa-money'],'xepan_hr_deduction');
                    $m->addItem(['Salary Sheet','icon'=>'fa fa-money'],'xepan_hr_salarysheet');

                    $m->addItem(['User','icon'=>'fa fa-user'],$this->app->url('xepan_hr_user',['status'=>'Active']));
                    $m->addItem(['Affiliate','icon'=>'fa fa-user'],$this->app->url('xepan_hr_affiliate',['status'=>'Active']));
                    $m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
                    $m->addItem(['Configuration','icon'=>'fa fa-cog fa-spin'],'xepan_hr_workingweekday');
                    $m->addItem(['Deactivate Request','icon'=>'fa fa-user'],'xepan_hr_employee_deactivaterequest');
                }
                
            }
            
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
            $this->app->user_menu->addItem(['My HR','icon'=>'fa fa-cog'],'xepan_hr_employee_leave');
            $this->app->user_menu->addItem(['Analytical Reports','icon'=>'fa fa-dashboard'],'xepan_hr_graphicalreport_builder');
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
            // $this->app->layout->add('xepan\base\View_Message',null,'message_view');

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
        
        // $this->getEmailAndMsgCount();
        $acl_m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>['access_level'=>'DropDown'],'config_key'=>'ACLMode','application'=>'hr']);
        $acl_m->tryLoadAny();

        $this->app->ACLModel = $acl_m['access_level'];

        /*================================*/
        $this->app->addHook('epan_dashboard_page',[$this,'epanDashboard']);
        $this->app->addHook('widget_collection',[$this,'exportWidgets']);
        $this->app->addHook('entity_collection',[$this,'exportEntities']);
        return $this;
    }

    function setup_pre_frontend(){
        if($this->app->auth->isLoggedIn()){   
            $this->app->employee = $this->add('xepan\hr\Model_Employee')->tryLoadBy('user_id',$this->app->auth->model->id);
            // $this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);        
        }else{
            $this->app->employee = $this->add('xepan\hr\Model_Employee');
        }
        return $this;
    }

    function setup_frontend(){
        $this->addAppFunctions();
        $this->routePages('xepan_hr');
        $this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
        ->setBaseURL('./vendor/xepan/hr/');

        if(isset($this->app->employee)){
            $this->app->addHook('communication_created',[$this->app->employee,'communicationCreatedNotify']);
        }
        $acl_m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>['access_level'=>'DropDown'],'config_key'=>'ACLMode','application'=>'hr']);
        $acl_m->tryLoadAny();
        $this->app->ACLModel = $acl_m['access_level'];
        return $this;
    }

    // function getEmailAndMsgCount(){

    //     if($this->app->stickyGET('update_email_message_counts')){
    //         // collect and run js()->execute()
    //         // to update various divs
    //         $my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
    //         $my_email->addExpression('post_email')->set(function($m,$q){
    //          return $q->getField('email_username');
    //         });

    //         $contact_email=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');
    //         $or = $contact_email->dsql()->orExpr();
    //         $i=0;
    //          foreach ($my_email as $email) {
    //              $or->where('mailbox','like',$email['post_email'].'%');
    //              $i++;
    //          }
    //          if($i == 0) $or->where('mailbox',-1);       
            
            
    //         $contact_email->addCondition($or);
    //         $contact_email->addCondition('status','Received');
    //         $contact_email->addCondition('is_read',false);
    //         $contact_count=$contact_email->count()->getOne();
    //         // throw new \Exception($contact_email['is_read_contact'], 1);
    //         $all_email=$this->add('xepan\communication\Model_Communication_Email_Received');
    //         $or =$all_email->dsql()->orExpr();
    //         $i=0;
    //             foreach ($my_email as $email) {
    //                 $or->where('mailbox','like',$email['post_email'].'%');
    //                 $i++;
    //             }
    //             if($i == 0) $or->where('mailbox',-1);       
            
            
    //         $all_email->addCondition($or);
    //         $all_email->addCondition('is_read',false);
    //         $all_count=$all_email->count()->getOne();
            
       
    //         /*Message Count*/

    //         $unread_msg_m = $this->add('xepan\communication\Model_Communication_AbstractMessage');
    //         $unread_msg_m->addCondition('is_read',false);
    //         $unread_emp_message_count = $unread_msg_m->count()->getOne();

    //         /*================================*/
    //         $js=[];
    //         $js[] = $this->app->js()->html($unread_emp_message_count)->_selector('.contact-and-all-message-count a span.atk-swatch-');
    //         $js[] = $this->app->js()->html($unread_emp_message_count)->_selector('.inter-msg-count');
    //         $js[] = $this->app->js()->html($contact_count." / ". $all_count)->_selector('.contact-and-all-email-count a span.atk-swatch-');
    //         $this->app->js(null,$js)->execute();
    //     }

    //     if(!$this->app->isAjaxOutput()){
    //         $this->app->js(true)->univ()->ajaxec($this->api->url('.',['update_email_message_counts'=>true,'cut_page']));
    //     }        
    // }

    function exportWidgets($app,&$array){
        // $array['widget_list'][] = 'xepan\base\Widget';
        $array[] = ['xepan\hr\Widget_MyTodaysAttendance','level'=>'Global','title'=>'My Todays Attendance'];
        $array[] = ['xepan\hr\Widget_EmployeeMovement','level'=>'Global','title'=>'Employees Attendance'];
        $array[] = ['xepan\hr\Widget_AvailableWorkforce','level'=>'Global','title'=>'Workforce Available'];
        $array[] = ['xepan\hr\Widget_AverageWorkHour','level'=>'Global','title'=>'Employees Average Working Hours'];
        $array[] = ['xepan\hr\Widget_LateComing','level'=>'Global','title'=>'Employees Average Late Arrivals'];
        $array[] = ['xepan\hr\Widget_TotalLateComing','level'=>'Global','title'=>'Companies Late Arrival And Extra Work'];
        
        $array[] = ['xepan\hr\Widget_DepartmentAvailableWorkforce','level'=>'Department','title'=>'Department Workforce Available'];
        $array[] = ['xepan\hr\Widget_DepartmentAverageWorkHour','level'=>'Department','title'=>'Department Average WorkHour'];
        $array[] = ['xepan\hr\Widget_DepartmentEmployeeAttendance','level'=>'Department','title'=>'Department Employee Attendance'];
        $array[] = ['xepan\hr\Widget_DepartmentLateComing','level'=>'Department','title'=>'Department Late Coming'];
        
        $array[] = ['xepan\hr\Widget_MyLateComing','level'=>'Individual','title'=>'My Late Arrivals'];
        $array[] = ['xepan\hr\Widget_MyCommunication','level'=>'Individual','title'=>'My Communication'];
        $array[] = ['xepan\hr\Widget_MyAverageWorkHour','level'=>'Individual','title'=>'My Average Working Hours'];
    }

    function exportEntities($app,&$array){
        $array['employee'] = ['caption'=>'Employee', 'type'=>'xepan\base\Basic','model'=>'xepan\hr\Model_Employee'];
        $array['department'] = ['caption'=>'Department', 'type'=>'DropDown','model'=>'xepan\hr\Model_Department'];
        $array['post'] = ['caption'=>'Post', 'type'=>'DropDown','model'=>'xepan\hr\Model_Post'];
        $array['Affiliate'] =['caption'=>'Affiliate','type'=>'DropDown','model'=>'xepan\hr\Model_Affiliate'];
        // $array['ACLMode'] = ['caption'=>'ACLMode','type'=>'DropDown','model'=>'xepan\hr\Model_Affiliate'];
        $array['Deduction'] = ['caption'=>'Deduction','type'=>'DropDown','model'=>'xepan\hr\Model_Deduction'];
        $array['LeaveTemplate'] = ['caption'=>'LeaveTemplate','type'=>'DropDown','model'=>'xepan\hr\Model_LeaveTemplate'];
        $array['LeaveTemplateDetail'] = ['caption'=>'LeaveTemplate','type'=>'DropDown','model'=>'xepan\hr\Model_LeaveTemplate'];
        $array['Leave'] = ['caption'=>'Leave','type'=>'DropDown','model'=>'xepan\hr\Model_Leave'];
        $array['Employee_Leave'] = ['caption'=>'Employee_Leave','type'=>'DropDown','model'=>'xepan\hr\Model_Employee_Leave'];
        $array['SalarySheet'] = ['caption'=>'SalarySheet','type'=>'DropDown','model'=>'xepan\hr\Model_SalarySheet'];
        $array['Reimbursement'] = ['caption'=>'Reimbursement','type'=>'DropDown','model'=>'xepan\hr\Model_Reimbursement'];
        $array['OfficialHoliday'] = ['caption'=>'OfficialHoliday','type'=>'DropDown','model'=>'xepan\hr\Model_OfficialHoliday'];
        $array['Report_Executor'] = ['caption'=>'Report_Executor','type'=>'DropDown','model'=>'xepan\hr\Model_ReportExecutor'];
    }

    // function epanDashboard($app,$page){

    //     $attan_m = $this->add("xepan\hr\Model_Employee_Attandance");
    //     $attan_m->addCondition('employee_id',$this->app->employee->id);
    //     $attan_m->addCondition('fdate',$this->app->today);
    //     $attan_m->setOrder('id','desc');
    //     $attan_m->tryLoadAny();

    //     if($attan_m['late_coming']>0){
    //         $page->add('xepan\base\View_Widget_SingleInfo',null,'top_bar')
    //                 ->setIcon('fa fa-thumbs-down')
    //                 ->setHeading(date('h:i A', strtotime($attan_m['from_date'])).' ! YOUR ARE LATE BY ')
    //                 ->setValue($attan_m['late_coming'].' Minutes')
    //                 ->makeDanger()
    //                 ->addClass('col-md-4')
    //                 ;
    //     }else{
    //         $page->add('xepan\base\View_Widget_SingleInfo',null,'top_bar')
    //                 ->setIcon('fa fa-thumbs-up')
    //                 ->setHeading(date('h:i A', strtotime($attan_m['from_date'])).' ! YOUR ARE EARLY BY ')
    //                 ->setValue(abs($attan_m['late_coming']).' Minutes')
    //                 ->makeSuccess()
    //                 ->addClass('col-md-4')
    //                 ;
    //     }
    // }



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

        // Default Widgets 
        $path=realpath(getcwd().'/vendor/xepan/hr/defaultReports');
        
        if(file_exists($path)){
            foreach (new \DirectoryIterator($path) as $file) {
                 if($file->isDot()) continue;
                // echo $path."/".$file;
                 $json= file_get_contents($path."/".$file);
                 $import_model = $this->add('xepan\base\Model_GraphicalReport');
                 $import_model->importJson($json);
            }
        }   
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
                    ->addCondition('in_time','10:00:00')
                    ->addCondition('out_time','18:00:00')
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

    function addAppFunctions(){
        $this->app->addMethod('immediateAppove',function($app,$namesapce=null){
            if($this->app->employee['scope'] === 'SuperUser')
                if($this->app->getConfig('all_rights_to_superuser',true))
                    return true;
            if($this->app->ACLModel === "none")
                return true;
            if($this->app->ACLModel === "Departmental"){

                $id= 0;
                if($namesapce instanceof \xepan\base\Model_Epan_InstalledApplication)
                    $id = $namesapce->id;
                elseif (is_string($namesapce)) {
                    $m = $this->add('xepan\base\Model_Epan_InstalledApplication')
                    ->addCondition('application_namespace',$namesapce)
                    ->tryLoadAny();

                    if($m->loaded()){
                        $id = $m->id;
                    }else
                        $id = 0;
                }elseif (is_int($namesapce)) {
                    $id = $namesapce;
                }

                $install_app = $this->add('xepan\hr\Model_EmployeeDepartmentalAclAssociation');
                $install_app->addCondition('employee_id',$this->app->employee->id);
                $install_app->addCondition('installed_app_id',$id);

                return $install_app->count()->getOne();
            }
            return false;
        });
    }    
}
