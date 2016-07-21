<?php

namespace xepan\hr;

class page_activity extends \xepan\base\Page{
	public $title="Activities";
	function init(){
		parent::init();

		$activity=$this->add('xepan\hr\Model_Activity');
		// $activity->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');
		
		$g=$this->add('xepan\hr\Grid',null,null,['view/activity/activities-info']);
		$g->setModel($activity);


		$g->addPaginator(25);

	}
}