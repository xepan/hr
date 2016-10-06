<?php

namespace xepan\hr;

class page_activity extends \xepan\base\Page{
	public $title="Activities";
	function init(){
		parent::init();

		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$contact_id = $this->app->stickyGET('contact_id');
		$related_person_id = $this->app->stickyGET('related_person_id');
		$department_id = $this->app->stickyGET('department_id');

		$custom_date = strtotime(date("Y-m-d", strtotime('-1 month', strtotime($this->app->today))));
		
		$toggle_button = $this->add('Button',null,'toggle_button')->set('Show/Hide form')->addClass('btn btn-primary btn-sm xepan-push-small');

		$form_view = $this->add('View',null,'form_view');
		$form = $form_view->add('Form');
		$form->setlayout('form\activity');
		$form->addField('DatePicker','from_date')->validate('required')->set($custom_date);
		$form->addField('DatePicker','to_date')->validate('required')->set($this->app->today);
		$form->addField('xepan\base\Basic','contact','Created By')->setModel($this->add('xepan\base\Model_Contact'));
		$form->addField('xepan\base\Basic','related_person','Related Person')->setModel($this->add('xepan\base\Model_Contact'));
		$form->addField('Dropdown','department','Department')->setModel($this->add('xepan\hr\Model_Department'));
		$form->addSubmit("FILTER")->addClass('btn btn-block btn-primary');

		$toggle_button->js('click',$form_view->js()->toggle());

		$activity_view = $this->add('xepan\base\View_Activity',['from_date'=>$from_date,'to_date'=>$to_date,'contact_id'=>$contact_id,'related_person_id'=>$related_person_id,'department_id'=>$department_id],'activity_view');

		if($form->isSubmitted()){
			$form->js(null,$activity_view->js()
											->reload(
												[
													'from_date'=>$form['from_date'],
													'to_date'=>$form['to_date'],
													'contact_id'=>$form['contact'],
													'related_person_id'=>$form['related_person'],
													'document_id'=>$form['document'],
													'department_id'=>$form['department']
												]))->univ()->successMessage('wait ... ')->execute();

		}
	}

	function defaultTemplate(){
		return ['page\activity'];
	}
}