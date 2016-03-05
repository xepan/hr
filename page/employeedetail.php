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

class page_employeedetail extends \Page {
	public $title='Employee Details';

	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';

		$employee= $this->add('xepan\hr\Model_Employee')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		
		
		$contact_view = $this->add('xepan\base\View_Contact',null,'contact_view');
		$contact_view->setModel($employee);

		if($employee->loaded()){
			$portfolio_view = $this->add('xepan\hr\View_Document',['action'=> $action],'portfolio_view',['page/employee/portfolio']);
			$portfolio_view->setModel($employee,['department','post'],['department_id','post_id']);
			$q = $portfolio_view->addMany('Qualification',null,'Qualification',['view/employee/qualification-grid']);
			$q->setModel($employee->ref('Qualifications'));

			$e = $portfolio_view->addMany('Experiences',null,'Experiences',['view/employee/experience-grid']);
			$e->setModel($employee->ref('Experiences'));


			$document_view = $this->add('xepan\hr\View_Document',['action'=> $action],'document_view',['page/employee/emp-document']);
			$q = $document_view->addMany('EmployeeDocument',null,'Document',['view/employee/emp-document-grid']);
			$q->setModel($employee->ref('EmployeeDocuments'));

			$activity_view = $this->add('xepan\base\Grid',null,'activity_view',['view/activity/activity-grid']);

			$activity=$this->add('xepan\base\Model_Activity');
			$activity->addCondition('contact_id',$_GET['contact_id']);
			$activity->tryLoadAny();
			$activity_view->setModel($activity);

			$form = $this->add('Form',null,'personal_info');
			$form->addField('Password','old_password');
			$form->addField('Password','new_password');
			$form->addField('Password','re_password');

			$sf = $this->add('Form',null,'emails');
			$field = $sf->addField('Hidden','permissions')->set(json_encode($employee->getPermissionEmail()));
			$email=$sf->add('xepan\base\Grid',null,null,['view/employee/email-grid']);
			$email->setModel($this->app->epan->ref('EmailSettings'));
			$email->addSelectable($field);
			$sf->addSubmit('Update');

			if($sf->isSubmitted()){
				$employee->removePermissionEmail();
				$emails_permission =$this->add('xepan\hr\Model_Email_Permission'); 
				$selected_emails=array();
				$selected_emails = json_decode($sf['permissions'],true);
				foreach ($selected_emails as $junk_id){
					$emails_permission['employee_id']=$employee->id;
					$emails_permission['emailsetting_id']=$junk_id;
					$emails_permission->saveAndUnload();
				}
				$sf->js(null,$sf->js()->univ()->successMessage('Emails updated for this employee'))->reload->execute();
			}

		}

	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}
}
