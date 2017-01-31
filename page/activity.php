<?php

namespace xepan\hr;

class page_activity extends \xepan\base\Page{
	public $title="Activities";
	public $breadcrumb=['Home'=>'index','Activities'=>'#','Activity Report'=>'xepan_hr_activityreport'];
	public $descendants = [];
	public $model;

	function init(){
		parent::init();

		$this->js(true)->_load('moment.min')
        			   ->_load('daterangepicker1')
        			   ->_css('daterangepicker');

		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$contact_id = $this->app->stickyGET('contact_id');
		$related_person_id = $this->app->stickyGET('related_person_id');
		$department_id = $this->app->stickyGET('department_id');
		$communication_type = $this->app->stickyGET('communication_type');
		$self_activity = $this->app->stickyGET('self_activity');

		// $root_posts = $this->add('xepan\hr\Model_Post');
		// $root_posts->addCondition('parent_post_id',$this->app->employee['post_id']);
		// $root_posts->tryLoadAny();
		// $this->descendants[] = $this->app->employee['post_id'];

		// foreach ($root_posts as $post) {		
		// 	if($post['id'] == $post['parent_post_id']){
		// 		$this->descendants[] = $post->id;
		// 		continue; 
		// 	}

		// 	$this->descendantPosts($post);
		// }

		$this->descendants = $this->app->employee->ref('post_id')->descendantPosts();
			
		$custom_date = strtotime(date("Y-m-d", strtotime('-1 month', strtotime($this->app->today))));
		
		$toggle_button = $this->add('Button',null,'toggle_button')->set('Show/Hide form')->addClass('btn btn-primary btn-sm xepan-push-small');

		$form_view = $this->add('View',null,'form_view');
		$form = $form_view->add('Form');
		$form->setlayout('form\activity');
		$date_range_field = $form->addField('DateRangePicker','date_range')
								 ->setStartDate($this->app->now)
								 ->setEndDate($this->app->now)
								 ->getBackDatesSet();

		$form->addField('xepan\base\Basic','contact','Created By')->setModel($this->add('xepan\base\Model_Contact'));
		$form->addField('xepan\base\Basic','related_person','Related Person')->setModel($this->add('xepan\base\Model_Contact'));

		$dept_field = $form->addField('Dropdown','department','Department');
		$dept_field->setModel($this->add('xepan\hr\Model_Department'));
		$dept_field->setEmptyText('Please select');

		$form->addField('Dropdown','communication_type','Communication Type')->setValueList(['Email'=>'Email','Call'=>'Call','TeleMarketing'=>'TeleMarketing','Personal'=>'Personal','SMS'=>'SMS'])->setEmptyText('Please select a communication type');
		$form->addField('CheckBox','show_my_activity','');
		$form->addSubmit("FILTER")->addClass('btn btn-block btn-primary');

		$this->js(true,$form_view->js()->hide());
		$toggle_button->js('click',$form_view->js()->toggle());

		$activity_view = $this->add('xepan\base\View_Activity',['paginator_count'=>50,'self_activity'=>$self_activity,'descendants'=>$this->descendants,'from_date'=>$from_date,'to_date'=>$to_date,'contact_id'=>$contact_id,'related_person_id'=>$related_person_id,'department_id'=>$department_id,'communication_type'=>$communication_type],'activity_view');

		if($form->isSubmitted()){
			$_from_date = $date_range_field->getStartDate();
        	$_to_date = $date_range_field->getEndDate();						
						
			$form->js(null,$activity_view->js()
											->reload(
												[
													'from_date'=>$_from_date,
													'to_date'=>$_to_date,
													'contact_id'=>$form['contact'],
													'related_person_id'=>$form['related_person'],
													'document_id'=>$form['document'],
													'department_id'=>$form['department'],
													'communication_type'=>$form['communication_type'],
													'self_activity'=>$form['show_my_activity']
												]))->univ()->successMessage('wait ... ')->execute();

		}
	}

	// function descendantPosts($post){		
	// 	$this->descendants[] = $post->id;		
	// 	$sub_posts = $this->add('xepan\hr\Model_Post');
	// 	$sub_posts->addCondition('parent_post_id',$post->id);
		
	// 	foreach ($sub_posts as $sub_post){
	// 		if($sub_post['id'] == $sub_post['parent_post_id']){
	// 			$this->descendants[] = $sub_post->id;
	// 			continue; 
	// 		}

	// 		$this->descendantPosts($sub_post);
	// 	}
	// }

	function defaultTemplate(){
		return ['page\activity'];
	}
}