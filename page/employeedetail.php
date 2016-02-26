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
			$portfolio_view = $this->add('xepan\hr\View_Document',['action'=> $action],'portfolio_view',['employee-detail/portfolio']);
			$portfolio_view->setModel($employee,['department','post'],['department_id','post_id']);
			$q = $portfolio_view->addMany('Qualification',null,'Qualification',['employee-detail/grid/qualification-grid']);
			$q->setModel($employee->ref('Qualifications'));

			$e = $portfolio_view->addMany('Experiences',null,'Experiences',['employee-detail/grid/experience-grid']);
			$e->setModel($employee->ref('Experiences'));

			$form = $this->add('Form',null,'personal_info');
			$form->addField('Password','old_password');
			$form->addField('Password','new_password');
			$form->addField('Password','re_password');

		}

	}

	function defaultTemplate(){
		return ['page/employee-profile'];
	}
}
