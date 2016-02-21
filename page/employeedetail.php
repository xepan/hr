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

		$employee= $this->add('xepan\base\Model_Contact')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		$d = $this->add('xepan\base\View_Document',
				[
					'action'=>$this->api->stickyGET('action')?:'view', // add/edit
					'id_fields_in_view'=>'["all"]/["post_id","field2_id"]',
					'allow_many_on_add' => false, // Only visible if editinng,
					'view_template' => ['view/profile']
				],
				'contact_view'
			);
		$d->setModel($employee,null,['first_name','last_name','type']);	
		$d->addMany(
			$employee->ref('Emails'),
			$view_class='xepan\base\Grid',$view_options=null,$view_spot='Emails',$view_defaultTemplate=['view/profile','Emails'],$view_fields=null,
			$class='xepan\base\CRUD',$options=['grid_options'=>['defaultTemplate'=>['view/profile','Emails']]],$spot='Emails',$defaultTemplate=null,$fields=null
			);
	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}
}
