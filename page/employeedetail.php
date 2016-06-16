<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_employeedetail extends \xepan\base\Page {
	public $title='Employee Details';
	public $breadcrumb=['Home'=>'index','Employee'=>'xepan_hr_employee','Detail'=>'#'];

	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';

		$employee= $this->add('xepan\hr\Model_Employee')->tryLoadBy('id',$this->api->stickyGET('contact_id'));

		if($action=="add"){

			$contact_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view_full_width');
			$contact_view->document_view->effective_template->del('im_and_events_andrelation');
			$contact_view->document_view->effective_template->del('email_and_phone');
			$this->template->del('details');
			$contact_view->setStyle(['width'=>'50%','margin'=>'auto']);
		}else{
			$contact_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view');
		}
		
		$contact_view->setModel($employee);
		if($employee->loaded()){
			$portfolio_view = $this->add('xepan\hr\View_Document',['action'=> $action],'portfolio_view',['page/employee/portfolio']);
			$portfolio_view->setIdField('contact_id');
			$portfolio_view->setModel($employee,['department','post','user','remark'],['department_id','post_id','user_id','remark']);
			$f=$portfolio_view->form;

			if($f->isSubmitted()){
				$f->save();
				$f->js(null,$f->js()->univ()->successMessage('Updated'))->reload()->execute();				
			}

			$q = $portfolio_view->addMany('Qualification',['no_records_message'=>'No qualifications found'],'Qualification',['view/employee/qualification-grid']);
			$q->setModel($employee->ref('Qualifications'));

			$e = $portfolio_view->addMany('Experiences',['no_records_message'=>'No experience found'],'Experiences',['view/employee/experience-grid']);
			$e->setModel($employee->ref('Experiences'));

			$official_view = $this->add('xepan\hr\View_Document',['action'=> $action],'official_info',['view/employee/official-details']);
			$official_view->setIdField('contact_id');
			$official_view->setModel($employee,['offer_date','doj','contract_date','leaving_date','in_time','out_time'],
											   ['offer_date','doj','contract_date','leaving_date','in_time','out_time']);


			$document_view = $this->add('xepan\hr\View_Document',['action'=> $action],'document_view',['page/employee/emp-document']);
			$document_view->setIdField('contact_id');
			$q = $document_view->addMany('EmployeeDocument',['no_records_message'=>'No document found'],'Document',['view/employee/emp-document-grid']);
			$q->setModel($employee->ref('EmployeeDocuments'));

			$activity_view = $this->add('xepan\base\Grid',['no_records_message'=>'No activity found'],'activity_view',['view/activity/activity-grid']);
			$activity_view->add('xepan\base\Paginator',null,'Paginator');

			$activity=$this->add('xepan\base\Model_Activity')->setOrder('created_at','desc');
			$activity->addCondition('contact_id',$_GET['contact_id']);
			$activity->tryLoadAny();
			$activity_view->setModel($activity);


			// $this->add('xepan\hr\')

			// $form = $this->add('Form',null,'personal_info');
			// $form->addField('Password','old_password');
			// $form->addField('Password','new_password');
			// $form->addField('Password','re_password');

			// $sf = $this->add('Form',null,'emails');
			// $field = $sf->addField('Hidden','permissions')->set(json_encode($employee->getPermissionEmail()));
			// $email=$sf->add('xepan\base\Grid',null,null,['view/employee/email-grid']);
			// $email->setModel($this->app->epan->ref('EmailSettings'));
			// $email->template->tryDel('Pannel');
			// $email->addSelectable($field);
			// $sf->addSubmit('Update');

			// if($sf->isSubmitted()){
			// 	$employee->removePermissionEmail();
			// 	$emails_permission =$this->add('xepan\hr\Model_Email_Permission'); 
			// 	$selected_emails=array();
			// 	$selected_emails = json_decode($sf['permissions'],true);
			// 	foreach ($selected_emails as $junk_id){
			// 		$emails_permission['employee_id']=$employee->id;
			// 		$emails_permission['emailsetting_id']=$junk_id;
			// 		$emails_permission->saveAndUnload();
			// 	}
			// 	$sf->js(null,$sf->js()->univ()->successMessage('Emails updated for this employee'))->reload->execute();
			// }

		}

	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}
}
