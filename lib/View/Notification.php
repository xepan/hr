<?php
namespace xepan\hr;
class View_Notification extends \CompleteLister{

	public $vp;

	function init(){
		parent::init();
		
		$this->js('reload')->reload();			

		// get new unread notifications
		// append them in template by js
		
		$notifications = $this->add('xepan\base\Model_Activity');
		// $notifications->addCondition('id','>',$this->app->employee['notified_till']?:0);
		$notifications->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');
		$notifications->setOrder('id','desc');
		$this->setModel($notifications)->setLimit(5);
		
		$notifications = $this->add('xepan\base\Model_Activity');
		$notifications->addCondition('id','>',$this->app->employee['notified_till']?:0);
		$notifications->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');

		$this->template->setHTML('icon','envelope-o');
		$this->template->set('notification_count',$notifications->count()->getOne());
		$this->template->set('unread_notification',$notifications->count()->getOne());

		if($this->app->recall('mute_all_notification',false)){
			$this->template->trySet('notif-class','fa fa-play');
			$this->template->trySet('notif-text', 'Play Notifications');
		}else{
			$this->template->trySet('notif-class','fa fa-pause');
			$this->template->trySet('notif-text', 'Pause Notifications');
		}

		if($_GET['notification_mute_toggle']){
			if($this->app->recall('mute_all_notification',false))
				$this->app->memorize('mute_all_notification',false);
			else	
				$this->app->memorize('mute_all_notification',true);

			$this->js()->_selector('.xepan-notification-view')->trigger('reload')->execute();
		}

		$js_event = [
						$this->js()->univ()->ajaxec($this->api->url('.',['notification_mute_toggle'=>true])),
						$this->js()->redirect($this->app->url())
					];
		$this->js('click',$js_event)->_selector('.play-pause-notifications');
		// $this->js('click')->_selector('.play-pause-notifications')->univ()->location($this->app->url(null,['notification_mute_toggle'=>true]));
		

		if($this->app->getConfig('websocket-notifications',false)){
			if(!$this->app->recall('mute_all_notification',false))
				$this->app->js(true)->_load('websocketclient')->univ()->runWebSocketClient($this->app->getConfig('websocket-server',false),$this->app->current_website_name.'_'.$this->app->employee->id);
			
			// $this->app->js(true,'$.wakeUp(function(sleep_time){'.(string)$this->app->js()->reload().';});')->_load('jquery.wakeup');
		}else{
			// No WebSocket implemented, keep 2 minute refresh method activated
			$this->js(true)->univ()->setInterval($this->js()->univ()->ajaxec($this->api->url('xepan_hr_notificationexec'))->_enclose(),120000);
		}
		
		// $this->on('click','.play-pause-notifications',function($js,$data){
		// 	if($this->app->recall('mute_all_notification',false))
		// 		$this->app->memorize('mute_all_notification',false);
		// 	else	
		// 		$this->app->memorize('mute_all_notification',true);
			
		// 	return $this->js()->_selector('.xepan-notification-view')->trigger('reload');
		// });
	}

	function formatRow(){
		$this->current_row['notification']=$this->model['notification']?$this->model['notification']:$this->model['activity'];
		return parent::formatRow();
	}

	function render(){
		$this->js(true)->_library('PNotify.desktop')->permission();
		$this->js(true)->_load('xepan.pnotify')->univ()->ajaxec($this->api->url('xepan_hr_notificationexec'));
		return parent::render();
	}

	function getJSID(){
		return "notificationid";
	}

	function defaultTemplate(){
		return ['view/notification'];
	}
}