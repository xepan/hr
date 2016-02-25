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

		$employee= $this->add('xepan\hr\Model_Employee')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		
		$contact_view = $this->add('xepan\base\View_Contact',null,'contact_view');
		$contact_view->setModel($employee);

		if($employee->loaded()){
			$portfolio_view = $this->add('xepan\base\View_Document',
					[
						'action'=>$this->api->stickyGET('action')?:'view', // add/edit
						'id_fields_in_view'=>[],
						'allow_many_on_add' => false, // Only visible if editinng,
						'view_template' => ['employee-detail/portfolio'],
						'submit_button'=>'Update',
						'id_field_on_reload'=>'contact_id'
					],
					'portfolio_view'

				);
			$portfolio_view->setModel($employee,['department','post'],['department_id','post_id']);

			$form = $this->add('Form',null,'personal_info');
			$form->addField('Password','old_password');
			$form->addField('Password','new_password');
			$form->addField('Password','re_password');

			// $personal_emails = $this->add('xepan\base\View_Document');
			// $personal_emails->setModel($employee->ref('Emails'));
						
			// 	$personal_view->addMany(
			// 		$employee->ref('Emails'),
			// 		$view_class='xepan\base\Grid',$view_options=null,$view_spot='Emails',$view_defaultTemplate=['employee-detail/grid/email-grid'],$view_fields=null,
			// 		$class='xepan\base\CRUD',$options=['grid_options'=>['defaultTemplate'=>['view/contact','Emails']]],$spot='Emails',$defaultTemplate=null,$fields=null
			// 		);
			
			// $activity_view = $this->add('xepan\base\View_Document',
			// 		[
			// 			'action'=>$this->api->stickyGET('action')?:'view', // add/edit
			// 			'id_fields_in_view'=>'["all"]/["post_id","field2_id"]',
			// 			'allow_many_on_add' => false, // Only visible if editinng,
			// 			'view_template' => ['employee-detail/activity']
			// 		],
			// 		'activity_view'

			// 	);
			// $activity_view->setModel($employee,null,['first_name','last_name']);
		
			// $emp_doc_view = $this->add('xepan\base\View_Document',
			// 		[
			// 			'action'=>$this->api->stickyGET('action')?:'view', // add/edit
			// 			'id_fields_in_view'=>'["all"]/["post_id","field2_id"]',
			// 			'allow_many_on_add' => false, // Only visible if editinng,
			// 			'view_template' => ['employee-detail/emp-document']
			// 		],
			// 		'document_view'

			// 	);
			// $emp_doc_view->setModel($employee,null,['first_name','last_name']);
			
			
		}

	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}
}
