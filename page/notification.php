<?php

namespace xepan\hr;

class page_notification extends \Page{
	public $title="Activity Notification";
	function init(){
		parent::init();

		$activity=$this->add('xepan\hr\Model_Activity');
		$activity->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');
		
		$g=$this->add('xepan\hr\Grid',null,null,['view/activity/activity-grid']);
		$g->setModel($activity);

		$g->addPaginator(50);

	}
}