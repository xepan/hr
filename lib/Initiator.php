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

            if($this->app->getConfig('keep_alive_time',false) !== false)
                $this->app->js(true)->univ()->setInterval($this->app->js()->univ()->ajaxec($this->api->url('.',['keep_alive_signal'=>true]))->_enclose(),$this->app->getConfig('keep_alive_time'));
            
            
            if(!($this->app->employee = $this->app->recall($this->app->epan->id.'_employee',false))){
                
                $this->app->employee = $this->add('xepan\hr\Model_Employee')->tryLoadBy('user_id',$this->app->auth->model->id);
                $this->app->employee_post = $this->app->employee->ref('post_id');
                $this->app->branch = $this->app->employee->ref('branch_id');

                $this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);
                $this->app->memorize($this->app->epan->id.'_employee_post', $this->app->employee_post);
                $this->app->memorize($this->app->epan->id.'_branch', $this->app->branch);
            }

            $this->app->employee_post = $this->app->recall($this->app->epan->id.'_employee_post');
            $this->app->employee_post = $this->app->employee->ref('post_id');
            
            $this->app->branch = $this->app->recall($this->app->epan->id.'_branch');
            
            // branch dropdown
            $branch_model = $this->add('xepan\base\Model_Branch');
            if(!$this->app->employee['branch_id'] AND $branch_model->count()->getOne()){

                $view = $this->app->page_top_right_button_set->add('View')->addClass("btn");
                $form = $view->add('Form',null,null,['form/horizontal']);
                $branch_field = $form->addField('DropDown','set_branch');
                $branch_field->setModel($branch_model);
                $branch_field->setEmptyText('Select Branch');
                if($this->app->branch){
                    $branch_field->set($this->app->branch->id);
                }
                $branch_field->js('change',$form->js()->submit());
                
                if($form->isSubmitted()){
                    if(!$form['set_branch']){
                        $this->app->forget($this->app->epan->id.'_employee');
                        $this->app->forget($this->app->epan->id.'_branch');
                    }else{
                        $this->app->branch = $branch_model->load($form['set_branch']);
                        $this->app->memorize($this->app->epan->id.'_branch', $branch_model);
                    }
                    $form->js(null,$this->app->redirect($this->app->url()))->univ()->successMessage('Your current working branch is updated')->execute();
                }
            }
            // if($this->app->employee['track_geolocation']){
            //     $this->app->js(true)->_load('track_geolocation3')->univ()->xepan_track_geolocation(18000000); // 5 minutes
            // }

            if($this->app->inConfigurationMode)
                $this->populateConfigurationMenus();
            else{
                $this->populateApplicationMenus();
                $this->setupXECTopMenus();
            }

            if(!($this->app->application_title = $this->app->recall('application_title'))){
                $company = $this->add('xepan\base\Model_Config_CompanyInfo')->tryLoadAny();
                $this->app->application_title = $this->app->employee['name']. ' ['.$this->app->employee['post'].'] - '. $company['company_name']. ' (Xavoc ERP / CRM )';
                $this->app->memorize('application_title',$this->app->application_title);
            }

            if(@$this->app->application_title){
                $this->app->template->trySet('app_title',$this->app->application_title);
            }

            if(!isset($this->app->resetDB) && !$this->app->employee->loaded()){
                throw new \Exception('User is not Employee', 1);
                
                // $this->createDefaultEmployee();
                // $this->app->redirect('.');
                // exit;
            }
            
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
            $this->app->addHook('user_loggedout',[$this->app->employee,'userLoggedout']);
            
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
        $this->app->addHook('user_loggedout',[$this->app->employee,'userLoggedout']);
        
        // $this->getEmailAndMsgCount();
        $acl_m = $this->add('xepan\base\Model_ConfigJsonModel',['fields'=>['access_level'=>'DropDown'],'config_key'=>'ACLMode','application'=>'hr']);
        $acl_m->tryLoadAny();

        $this->app->ACLModel = $acl_m['access_level'];

        /*================================*/
        $this->app->addHook('epan_dashboard_page',[$this,'epanDashboard']);
        $this->app->addHook('widget_collection',[$this,'exportWidgets']);
        $this->app->addHook('entity_collection',[$this,'exportEntities']);
        $this->app->addHook('collect_shortcuts',[$this,'collect_shortcuts']);
        return $this;
    }

    function populateConfigurationMenus(){
        $m = $this->app->top_menu->addMenu('HR');
        $m->addItem(['Working Week Days','icon'=>'fa fa-calendar'],$this->app->url('xepan_hr_workingweekday'));
        $m->addItem(['Official Holidays','icon'=>'fa fa-calendar'],$this->app->url('xepan_hr_officialholiday'));
        $m->addItem(['Leave Template','icon'=>'fa fa-file-o'],$this->app->url('xepan_hr_configleave'));
        $m->addItem(['Salary Template','icon'=>'fa fa-file-o'],$this->app->url('xepan_hr_configsalary'));
        $m->addItem(['Misc Config','icon'=>'fa fa-cog fa-spin'],$this->app->url('xepan_hr_miscconfig'));
        $m->addItem(['Pay Slip Layouts','icon'=>'fa fa-th'],$this->app->url('xepan_hr_layouts'));
        $m->addItem(['ACL Level','icon'=>'fa fa-th'],$this->app->url('xepan_hr_aclconfig'));
        $m->addItem(['Report Executor','icon'=>'fa fa-th'],$this->app->url('xepan_hr_reportexecutor'));
    }

    function populateApplicationMenus(){
        if(!$this->app->getConfig('hidden_xepan_hr',false)){
                // $m = $this->app->top_menu->addMenu('HR');
                // // $m->addItem(['Dashboard','icon'=>'fa fa-dashboard'],$this->app->url('xepan_hr_dashboard'));
                // $m->addItem(['Department','icon'=>'fa fa-sliders'],$this->app->url('xepan_hr_department',['status'=>'Active']));
                // $m->addItem(['Post','icon'=>'fa fa-sitemap'],$this->app->url('xepan_hr_post',['status'=>'Active']));
                // $m->addItem(['Employee','icon'=>'fa fa-male'],$this->app->url('xepan_hr_employee',['status'=>'Active']));
                
                // if(!$this->app->getConfig('base_hr_only',false)){   
                //     $m->addItem(['Employee Attendance','icon'=>'fa fa-check-square-o'],'xepan_hr_attandance');
                //     $m->addItem(['Employee Movement','icon'=>'fa fa-eye'],'xepan_hr_employeemovement');
                //     $m->addItem(['Leave Management','icon'=>'fa fa-eye'],'xepan_hr_leavemanagment');
                //     $m->addItem(['Reimbursement Management','icon'=>'fa fa-money'],'xepan_hr_reimbursement');
                //     $m->addItem(['Deduction Management','icon'=>'fa fa-money'],'xepan_hr_deduction');
                //     $m->addItem(['Salary Sheet','icon'=>'fa fa-money'],'xepan_hr_salarysheet');

                //     $m->addItem(['User','icon'=>'fa fa-user'],$this->app->url('xepan_hr_user',['status'=>'Active']));
                //     $m->addItem(['Affiliate','icon'=>'fa fa-user'],$this->app->url('xepan_hr_affiliate',['status'=>'Active']));
                //     $m->addItem(['ACL','icon'=>'fa fa-dashboard'],'xepan_hr_aclmanagement');
                //     // $m->addItem(['Configuration','icon'=>'fa fa-cog fa-spin'],'xepan_hr_workingweekday');
                //     $m->addItem(['Deactivate Request','icon'=>'fa fa-user'],'xepan_hr_employee_deactivaterequest');
                // }

                $this->app->user_menu->addItem(['Activity','icon'=>'fa fa-cog'],'xepan_hr_activity');
                $this->app->user_menu->addItem(['My HR','icon'=>'fa fa-cog'],'xepan_hr_employee_leave');
                $this->app->user_menu->addItem(['Analytical Reports','icon'=>'fa fa-dashboard'],'xepan_hr_graphicalreport_builder');
                // $m = $this->app->side_menu->addItem('HR');
                // $this->app->report_menu->addItem(['Employee Attandance Report','icon'=>'fa fa-users'],'xepan_hr_report_employeeattandance');

                /*Reports menu*/
                // $this->app->report_menu->addItem(['Employee Attendance Report','icon'=>'fa fa-users'],$this->app->url('xepan_hr_report_employeeattandance'));

                
            }
    }

    function setupXECTopMenus(){
        // if($this->recall('top_menu_array',false)){
        //     $this->app->top_menu_array = $this->recall('top_menu_array');
        // }

        if($post_menus = $this->app->employee_post['allowed_menus']){
            foreach (explode(",", $post_menus) as $post_menu) {
                $menu_config = $this->add('xepan\base\Model_Config_Menus')
                                ->addCondition('name',$post_menu)
                                ->tryLoadAny();
                
                if($menu_config->loaded()){
                    $arr = json_decode($menu_config['value'],true);
                    if(is_array($arr))
                        $this->app->top_menu_array = array_merge($this->app->top_menu_array, $arr);
                }
            }

            $this->memorize('top_menu_array',$this->app->top_menu_array);
            return;
        }
        

        $menu_config = $this->add('xepan\base\Model_Config_Menus')
                        ->addCondition('name','DEFAULT')
                        ->tryLoadAny();
        if($menu_config->loaded()){
            $this->app->top_menu_array = json_decode($menu_config['value'],true);
            $this->memorize('top_menu_array',$this->app->top_menu_array);
            return;
        }
    }

        // used for custom menu
    function getTopApplicationMenu(){
        if($this->app->getConfig('hidden_xepan_hr',false)){return [];}

        return ['HR'=>[
                    ['name'=>'Department',
                        'icon'=>'fa fa-sliders',
                        'url'=>'xepan_hr_department',
                        'url_param'=>['status'=>'Active']
                    ],
                    [
                        'name'=>'Post',
                        'icon'=>'fa fa-sitemap',
                        'url'=>'xepan_hr_post',
                        'url_param'=>['status'=>'Active']
                    ],
                    [
                        'name'=>'Employee',
                        'icon'=>'fa fa-male',
                        'url'=>'xepan_hr_employee',
                        'url_param'=>['status'=>'Active']
                    ],
                    [
                        'name'=>'Employee Attendance',
                        'icon'=>'fa fa-check-square-o',
                        'url'=>'xepan_hr_attandance'
                    ],
                    [   'name'=>'Employee Movement',
                        'icon'=>'fa fa-eye',
                        'url'=>'xepan_hr_employeemovement'
                    ],
                    [   'name'=>'Leave Management',
                        'icon'=>'fa fa-eye',
                        'url'=>'xepan_hr_leavemanagment'
                    ],
                    [   'name'=>'Reimbursement Management',
                        'icon'=>'fa fa-money',
                        'url'=>'xepan_hr_reimbursement'
                    ],
                    [   'name'=>'Deduction Management',
                        'icon'=>'fa fa-money',
                        'url'=>'xepan_hr_deduction'
                    ],
                    [   'name'=>'Salary Sheet',
                        'icon'=>'fa fa-money',
                        'url'=>'xepan_hr_salarysheet'
                    ],
                    [   'name'=>'User',
                        'icon'=>'fa fa-user',
                        'url'=>'xepan_hr_user',
                        'url_param'=>['status'=>'Active']
                    ],
                    [   'name'=>'Affiliate',
                        'icon'=>'fa fa-user',
                        'url'=>'xepan_hr_affiliate',
                        'url_param'=>['status'=>'Active']
                    ],
                    [   'name'=>'ACL',
                        'icon'=>'fa fa-dashboard',
                        'url'=>'xepan_hr_aclmanagement'
                    ],
                    [   'name'=>'Deactivate Request',
                        'icon'=>'fa fa-user',
                        'url'=>'xepan_hr_employee_deactivaterequest'
                    ],
                    [   'name'=>'Company Activity',
                        'icon'=>'fa fa-cog',
                        'url'=>'xepan_hr_activity',
                        'skip_default'=>true
                    ],
                    [   'name'=>'My HR',
                        'icon'=>'fa fa-cog',
                        'url'=>'xepan_hr_employee_leave',
                        'skip_default'=>true
                    ]
                ],
                'Reports'=>[
                    [
                        'name'=>'Employee Attendance Report',
                        'icon'=>'fa fa-users',
                        'url'=>'xepan_hr_report_employeeattandance'
                    ]
                ]

            ];
    }

    function getConfigTopApplicationMenu(){
        if($this->app->getConfig('hidden_xepan_hr',false)){return [];}

        return [
                'Hr_Config'=>[
                    [
                        'name'=>'Working Week Days',
                        'icon'=>'fa fa-calendar',
                        'url'=>'xepan_hr_workingweekday'
                    ],
                    [
                        'name'=>'Official Holidays',
                        'icon'=>'fa fa-calendar',
                        'url'=>'xepan_hr_officialholiday'
                    ],
                    [
                        'name'=>'Leave Template',
                        'icon'=>'fa fa-file-o',
                        'url'=>'xepan_hr_configleave'
                    ],
                    [
                        'name'=>'Salary Template',
                        'icon'=>'fa fa-file-o',
                        'url'=>'xepan_hr_configsalary'
                    ],
                    [
                        'name'=>'Misc Config',
                        'icon'=>'fa fa-cog',
                        'url'=>'xepan_hr_miscconfig'
                    ],
                    [
                        'name'=>'Pay Slip Layouts',
                        'icon'=>'fa fa-th',
                        'url'=>'xepan_hr_layouts'
                    ],
                    [
                        'name'=>'ACL Level',
                        'icon'=>'fa fa-cog',
                        'url'=>'xepan_hr_aclconfig'
                    ],
                    [
                        'name'=>'Report Executor',
                        'icon'=>'fa fa-th',
                        'url'=>'xepan_hr_reportexecutor'
                    ]
                ]
            ];

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
        $array['Employee'] = ['caption'=>'Employee', 'type'=>'xepan\base\Basic','model'=>'xepan\hr\Model_Employee'];
        $array['Department'] = ['caption'=>'Department', 'type'=>'DropDown','model'=>'xepan\hr\Model_Department'];
        $array['Post'] = ['caption'=>'Post', 'type'=>'DropDown','model'=>'xepan\hr\Model_Post'];
        $array['Affiliate'] =['caption'=>'Affiliate','type'=>'DropDown','model'=>'xepan\hr\Model_Affiliate'];
        // $array['ACLMode'] = ['caption'=>'ACLMode','type'=>'DropDown','model'=>'xepan\hr\Model_Affiliate'];
        $array['Deduction'] = ['caption'=>'Deduction','type'=>'DropDown','model'=>'xepan\hr\Model_Deduction'];
        $array['LeaveTemplate'] = ['caption'=>'LeaveTemplate','type'=>'DropDown','model'=>'xepan\hr\Model_LeaveTemplate'];
        $array['LeaveTemplateDetail'] = ['caption'=>'LeaveTemplate','type'=>'DropDown','model'=>'xepan\hr\Model_LeaveTemplate'];
        $array['Leave'] = ['caption'=>'Leave','type'=>'DropDown','model'=>'xepan\hr\Model_Leave'];
        $array['Employee_Leave'] = ['caption'=>'Employee_Leave','type'=>'DropDown','model'=>'xepan\hr\Model_Employee_Leave'];
        $array['SalarySheet'] = ['caption'=>'SalarySheet','type'=>'DropDown','model'=>'xepan\hr\Model_SalarySheet'];
        $array['SalaryTemplate'] = ['caption'=>'SalaryTemplate','type'=>'DropDown','model'=>'xepan\hr\Model_SalaryTemplate'];
        $array['Reimbursement'] = ['caption'=>'Reimbursement','type'=>'DropDown','model'=>'xepan\hr\Model_Reimbursement'];
        $array['OfficialHoliday'] = ['caption'=>'OfficialHoliday','type'=>'DropDown','model'=>'xepan\hr\Model_OfficialHoliday'];
        $array['Report_Executor'] = ['caption'=>'Report_Executor','type'=>'DropDown','model'=>'xepan\hr\Model_ReportExecutor'];
        $array['HR_HOLIDAY_BETWEEN_LEAVES'] = ['caption'=>'HR_HOLIDAY_BETWEEN_LEAVES','type'=>"DropDown",'model'=>'xepan\base\Model_ConfigJsonModel'];
        $array['HR_REIMBURSEMENT_SALARY_EFFECT'] = ['caption'=>'HR_REIMBURSEMENT_SALARY_EFFECT','type'=>"DropDown",'model'=>'xepan\base\Model_ConfigJsonModel'];
        $array['HR_DEDUCTION_SALARY_EFFECT'] = ['caption'=>'HR_DEDUCTION_SALARY_EFFECT','type'=>"DropDown",'model'=>'xepan\base\Model_ConfigJsonModel'];
        $array['HR_SALARY_DUE_ENTRY_AFFECT_EMPLOYEE_LEDGER'] = ['caption'=>'HR_SALARY_DUE_ENTRY_AFFECT_EMPLOYEE_LEDGER','type'=>"DropDown",'model'=>'xepan\base\Model_ConfigJsonModel'];
        
    }

    function collect_shortcuts($app,&$shortcuts){
        $shortcuts[]=["title"=>"Company Departments","keywords"=>"department","description"=>"Manage your company departments","normal_access"=>"HR -> Department","url"=>$this->app->url('xepan_hr_department',['status'=>'Active']),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Company Posts","keywords"=>"posts","description"=>"Manage your company posts","normal_access"=>"HR -> Post","url"=>$this->app->url('xepan_hr_post'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Employees","keywords"=>"employee staff members","description"=>"Manage your company employees","normal_access"=>"HR -> Employee","url"=>$this->app->url('xepan_hr_employee',['status'=>'Active']),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Employee Attendance","keywords"=>"attendance","description"=>"Manage Attendance","normal_access"=>"HR -> Employee Attendance","url"=>$this->app->url('xepan_hr_attandance'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Employee Leave Management","keywords"=>"leave","description"=>"Manage Employee Leaves","normal_access"=>"HR -> Leave Management","url"=>$this->app->url('xepan_hr_leavemanagment'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Employee Movement","keywords"=>"movement","description"=>"Employee movement","normal_access"=>"HR -> Employee Movement","url"=>$this->app->url('xepan_hr_employeemovement'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Employee Reimbursement Management","keywords"=>"reimbursement","description"=>"Employee reimbursement","normal_access"=>"HR -> Reimbursement Management","url"=>$this->app->url('xepan_hr_reimbursement'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Deducation Management","keywords"=>"deducation","description"=>"Employee deduction management","normal_access"=>"HR -> Deducation Management","url"=>$this->app->url('xepan_hr_deduction'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Salary Sheet","keywords"=>"salary sheet","description"=>"Employee salary sheet","normal_access"=>"HR -> Salary Sheet","url"=>$this->app->url('xepan_hr_salarysheet'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Users","keywords"=>"user login credentials password","description"=>"Manage users","normal_access"=>"HR -> User","url"=>$this->app->url('xepan_hr_user'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Affiliate","keywords"=>"affiliate","description"=>"Manage company affiliate","normal_access"=>"HR -> Affiliate","url"=>$this->app->url('xepan_hr_affiliate'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Working days in week & Attendance IP","keywords"=>"weekly work days allowed ip for attendance","description"=>"Manage your weekly working days & allowed IP for attendance","normal_access"=>"HR -> Configuration / SideBar -> Working Week Days","url"=>$this->app->url('xepan_hr_workingweekday'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Manage Official Holiday","keywords"=>"official holiday declaration","description"=>"Manage companies official holidays","normal_access"=>"HR -> Configuration / SideBar -> Official Holidays","url"=>$this->app->url('xepan_hr_officialholiday'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Leaves Configuration","keywords"=>"system leave paid CL PL Templates","description"=>"Configure companies leave templates","normal_access"=>"HR -> Configuration / SideBar -> Leave Template","url"=>$this->app->url('xepan_hr_configleave'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Salary Configuration","keywords"=>"system salary Basic HRA TA DA Templates","description"=>"Configure companies salary templates","normal_access"=>"HR -> Configuration / SideBar -> Salary Template","url"=>$this->app->url('xepan_hr_configsalary'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"HR Configurations","keywords"=>"holiday between leave reimbursement in salary deduction configuration employee ledger create","description"=>"Configure Basic rules like holiday between leaves, Reimbursement & Deduction in salary or separate or Maintain Each employee Ledger or not","normal_access"=>"HR -> Configuration / SideBar -> Misc Configuration","url"=>$this->app->url('xepan_hr_miscconfig'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Pay Slip Layout","keywords"=>"pay slip salary layout format design","description"=>"Design Salary slip layout for printable format","normal_access"=>"HR -> Configuration / SideBar -> Pay Slip Layout","url"=>$this->app->url('xepan_hr_layouts'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"ACL System","keywords"=>"acl configuration desabled allow all department base application document based","description"=>"Configure your ACL requirement, No ACL, Application base or document base","normal_access"=>"HR -> Configuration / SideBar -> ACL Level","url"=>$this->app->url('xepan_hr_aclconfig'),'mode'=>'frame'];
        $shortcuts[]=["title"=>"Report Executor","keywords"=>"custom report execute","description"=>"Allows you to create complex reports","normal_access"=>"HR -> Configuration / SideBar -> Report Executor","url"=>$this->app->url('xepan_hr_reportexecutor'),'mode'=>'frame'];
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

        $emp = $this->createDefaultEmployee();

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

        $emp['graphical_report_id'] = $this->add('xepan\base\Model_GraphicalReport')->tryLoadBy('name','GlobalReport')->get('id');
        $emp->save();
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
                    ->addCondition('permission_level','Global')
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

        return $emp;
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
