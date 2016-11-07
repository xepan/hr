<?php
namespace xepan\hr;
class View_Notification extends \CompleteLister{

	public $vp;

	function init(){
		parent::init();
		
		$this->js('reload')->reload();			
		
		$this->vp = $this->add('VirtualPage');
		$this->vp->set(function($p){
			if($this->app->recall('mute_all_notification',false))
				$p->js(null)->execute();

			$new_notificagions = $this->add('xepan\hr\Model_Activity')
									->addCondition('id','>',$this->app->employee['notified_till']?:0)
									->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%')
									->addCondition('created_at','<',$this->app->now)
									->setOrder('id','asc')
									->getRows();

			$js=[];
			if(count($new_notificagions)){
				foreach ($new_notificagions as $nt) {
					$js[] = $p->js()->univ()->notify(
							$nt['details']?$nt['activity']:'',  // title
							$nt['details']?:($nt['notification']?:$nt['activity']), //message
							'notice',true,undefined,false);
				}

				$this->add('xepan\hr\Model_Employee')
						->load($this->app->employee->id)
						->set('notified_till',$new_notificagions[count($new_notificagions)-1]['id'])
						->save();
				$this->app->employee->reload();
				$this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);
			}

			$p->js(null,$js)->execute();
			
			
		});

		// get new unread notifications
		// append them in template by js
		
		if(!$_GET[$this->vp->name]){	
		
			$notifications = $this->add('xepan\base\Model_Activity');
			$notifications->addCondition('id','>',$this->app->employee['notified_till']?:0);
			$notifications->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');

			$this->setModel($notifications)->setLimit(3);
			
			$this->template->setHTML('icon','envelope-o');
			$this->template->set('notification_count',$notifications->count()->getOne());
			$this->template->set('unread_notification',$notifications->count()->getOne());
		}

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

		$this->js(true)->univ()->setInterval($this->js()->univ()->ajaxec($this->api->url('.',[$this->vp->name=>'true']))->_enclose(),120000);
		$this->js('click',$this->js()->univ()->ajaxec($this->api->url('.',['notification_mute_toggle'=>true])))->_selector('.play-pause-notifications');
		
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
		$this->js(true)->_load('xepan.pnotify')->univ()->ajaxec($this->api->url('/',[$this->vp->name=>'true']));
		return parent::render();
	}

	function getJSID(){
		return "notificationid";
	}

	function defaultTemplate(){
		return ['view/notification'];
	}
}