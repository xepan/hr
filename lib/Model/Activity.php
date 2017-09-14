<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class Model_Activity extends \xepan\base\Model_Activity{

	function notifyWhoCan($list_of_actions,$current_statuses,$model=null,$notify_self=true, $msg=null){
		
		if(!$this->app->employee->loaded())
			return;

		
		$acl_m = $this->add('xepan\hr\Model_ACL');
		if(!$model)
			$model = $this->ref('related_document_id');

		$acl_m->addCondition('type',$model['type']);
	
		if(!is_array($list_of_actions)) $list_of_actions = explode(",", $list_of_actions);
		if(!is_array($current_statuses)) $current_statuses = explode(",", $current_statuses);

		
		$employee_ids=[];
		
		if($this->app->getConfig('all_notification_to_superuser',true)){
			foreach($this->add('xepan\hr\Model_Employee')->addCondition('scope','SuperUser')->getRows() as $emps){
				$employee_ids [] = $emps['id'];
			}
		}

		foreach ($acl_m as $acl) {
			// echo $acl['namespace'].' '. $acl['post']. ' ';
			foreach ($current_statuses as $current_status) {
				$actions = $acl['action_allowed'][$current_status];
				foreach ($list_of_actions as $req_act) {
					$text_code = $actions[$req_act]; // Self Only, All, None, Etc.
					// echo '<br/>-----'.$current_status. ' '.$req_act. ' ' . $text_code;
					switch ($text_code) {
						case 'Self Only':
							# if($model->created_by->post_id == $acl->post_id) include this id
							if(!$model['created_by_id'])
								break;
							if($this->getPost($model['created_by_id'])->get('id') == $acl['post_id'])
								$employee_ids[] = $model['created_by_id'];
							break;
						case 'All':
							# code...
							# include all employees under $acl->post_id
							if($acl->ref('post_id')->count()->getOne() && $acl->ref('post_id')->ref('Employees')){
								foreach ($acl->ref('post_id')->ref('Employees')->addCondition('status','Active') as $emp) {
									$employee_ids [] = $emp->id;
								}
							}
							break;
						case 'None':
							# code...
							break;
						default:
							# code...
							break;
					}
				}
			}
			// echo ' <br/>';
		}


		$employee_ids = array_unique($employee_ids, SORT_REGULAR);
		// throw new \Exception(print_r($employee_ids,true), 1);
		
		$this['notify_to'] = json_encode($employee_ids);
		if($msg) $this['notification'] = $msg;
		
		$this->save();

		return $this;
		// $this->pushToWebSocket($employee_ids,$this['notification']?:$this['activity']);
		
	}

	function notifyTo($employee_ids,$notification_msg,$related_document=null, $related_contact=null, $contact=null){
		if(!is_array($employee_ids)) throw $this->exception('employee_ids must be id of employees');

		if($related_document) $this['related_document_id'] = $related_document->id;
		if($related_contact) $this['related_contact_id'] = $related_contact->id;

		if(!$contact) $contact = $this->app->employee;
		if(!$this['contact_id']) $this['contact_id'] = $contact->id;

		
		$this['notification'] =$notification_msg;
		$this['notify_to'] = json_encode($employee_ids);
		$this->save();

		// $this->pushToWebSocket($employee_ids,$notification_msg);
		return $this;
	}

	function getPost($employee_id){
		if(!isset($this->emp_posts[$employee_id])){
			return $this->emp_posts[$employee_id] = $this->app->employee->newInstance()->load($employee_id)->ref('post_id');
		}

		return $this->emp_posts[$employee_id];
	}

}
