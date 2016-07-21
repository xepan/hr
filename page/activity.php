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

		$crud->grid->js('click')->_selector('.do-view-customer-detail')->univ()->frameURL('Customer Details',[$this->api->url('xepan_commerce_customerdetail'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-customer-id]')->data('id')]);
		}

		$g->addPaginator(25);

	}
}