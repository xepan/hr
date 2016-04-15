<?php
namespace xepan\hr;
class View_Notification extends \CompleteLister{

	public $vp;

	function init(){
		parent::init();

		session_write_close();		

		$this->vp = $this->add('VirtualPage');
		$this->vp->set(function($p){
			$new_notificagions = $this->add('xepan\hr\Model_Activity')
									->addCondition('id','>',$this->app->employee['notified_till']?:0)
									->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%')
									->getRows();

			$js=[];
			if(count($new_notificagions)){
				foreach ($new_notificagions as $nt) {
					$js[] = $p->js()->univ()->notify(
							$nt['details']?$nt['activity']:'',  // title
							$nt['details']?:($nt['notification']?:$nt['activity']), //message
							'warning',true,undefined,true);
				}

				$this->app->employee->set('notified_till',$nt['id'])->save();
			}

			$p->js(null,$js)->execute();
			
			
		});

		// get new unread notifications
		// append them in template by js
		
		if(!$_GET[$this->vp->name]){	
		
			$notifications = $this->add('xepan\base\Model_Activity');
			$notifications->addCondition('id','>',$this->app->employee['notified_till']?:0);
			// $notifications->addCondition('notify_to','like',','.$this->app->employee->id.',');

			$this->setModel($notifications)->setLimit(3);
			
			$this->template->setHTML('icon','envelope-o');
			$this->template->set('notification_count',rand(1,100));
			$this->template->set('unread_notification',rand(1,100));

			// $this->js(true)->univ()->setTimeout($this->js()->reload()->_enclose(),15000);
		}

	}

	function render(){		
		$this->js(true)
			->_load('pnotify.custom.min')
			->_css('pnotify.custom.min');
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