<?php

namespace xepan\hr;

class page_activity extends \xepan\base\Page{
	public $title="Activities";
	function init(){
		parent::init();

		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');
		$this->app->stickyGET('contact_id');

		$custom_date = strtotime(date("Y-m-d", strtotime('-1 month', strtotime($this->app->today))));

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validate('required')->set($custom_date);
		$form->addField('DatePicker','to_date')->validate('required')->set($this->app->today);
		$form->addField('xepan\base\Basic','contact')->setModel($this->add('xepan\base\Model_Contact'));

		$form->addSubmit("Filter");

		$activity_view = $this->add('xepan\base\View_Activity',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date'],'contact_id'=>$_GET['contact_id']]);

		if($form->isSubmitted()){
			$form->js(null,$activity_view->js()
											->reload(
												[
													'from_date'=>$form['from_date']?:0,
													'to_date'=>$form['to_date']?:0,
													'contact_id'=>$form['contact']?:0
												]))->univ()->successMessage('wait ... ')->execute();

		}
	}
}