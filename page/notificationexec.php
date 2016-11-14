<?php

namespace xepan\hr;

class page_notificationexec extends \xepan\base\Page{
	function init(){
		parent::init();
		$p = $this;
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
	}
}