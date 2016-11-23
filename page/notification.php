<?php

namespace xepan\hr;

class page_notification extends \xepan\base\Page{
	public $title="Activity Notification";
	function init(){
		parent::init();

		$activity=$this->add('xepan\hr\Model_Activity');
		$activity->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');		
		$g=$this->add('xepan\hr\Grid',null,null,['view/activity/activity-grid']);
		$g->setModel($activity);

		// $activity_m = $this->add('xepan\hr\Model_Activity');
		// $activity_m->addExpresion('doc_type')->set(function($m,$q){
		// 	return $
		// });
		// $counts = $count_m->_dsql()->del('fields')->field('status')->field('count(*) counts')->group('Status')->get();
		// $counts_redefined =[];
		// $total=0;
		// foreach ($counts as $cnt) {
		// 	$counts_redefined[$cnt['status']] = $cnt['counts'];
		// 	$total += $cnt['counts'];
		// }
		$g->addPaginator(50);

	}
}