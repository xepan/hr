<?php

namespace xepan\hr;

class page_notificationexec extends \xepan\base\Page{
	function init(){
		parent::init();
		$p = $this;
		if($this->app->recall('mute_all_notification',false)){
			$p->js(null)->execute();
		}
			
			$new_notificagions = $this->add('xepan\hr\Model_Activity')
									->addCondition('id','>',$this->app->employee['notified_till']?:0)
									->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%')
									->addCondition('created_at','<',$this->app->now)
									->setOrder('id','asc')
									->getRows();

			$js=[];
			if(count($new_notificagions)){
				foreach ($new_notificagions as $nt) {
					$title= "Notification";
					$type= "notice";
					$desktop = true;
					$sticky = true;
					$icon = null;

					$message=$nt['activity'];

					if($nt['notification']){
						if($this->isJson($nt['notification'])) $nt['notification'] = json_decode($nt['notification'],true);
						try{
							$message=$nt['notification']['message'];
							$title= $nt['notification']['tite']?:'Notification';
							$type= $nt['notification']['type']?:'notice';
							$desktop = $nt['notification']['desktop']?true:false;
							$sticky = $nt['notification']['sticky']?true:false;
							$icon = $nt['notification']['icon']?:null;
						}catch(\Exception $e){
							// var_dump($nt->data);
							// throw $e;
						}
					}

					$js[] = $p->js()->univ()->notify($title, $message, $type, $desktop, null, $sticky, $icon);
				}

				$this->add('xepan\hr\Model_Employee')
						->load($this->app->employee->id)
						->set('notified_till',$new_notificagions[count($new_notificagions)-1]['id'])
						->save();
				$this->app->employee->reload();
				$this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);
			}

			$p->js(null,$js)->execute();
	}

	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
}