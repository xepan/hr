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

	function notifyWhoCan($list_of_actions,$current_status,$document=null,$notify_self=true){
		$acl_m = $this->add('xepan\hr\Model_ACL');
		if(!$document)
			$document = $this->ref('related_document_id');

		$acl_m->addCondition('document_type',$document['type']);
	
		if(!is_array($list_of_actions)) $list_of_actions = explode(",", $list_of_actions);		

		$employee_ids=[];
		foreach ($acl_m as $acl) {
			$actions = $acl['action_allowed'][$current_status];

			foreach ($list_of_actions as $req_act) {
				$text_code = $actions[$req_act]; // Self Only, All, None, Etc.
				switch ($text_code) {
					case 'Self Only':
						# if($document->created_by->post_id == $acl->post_id) include this id
						if($this->getPost($document['created_by_id'])->get('id') == $acl['post_id'])
							$employee_ids[] = $document['created_by_id'];
						break;
					case 'All':
						# code...
						# include all employees under $acl->post_id
						foreach ($acl->ref('post_id')->ref('Employees') as $emp) {
							$employee_ids [] = $emp->id;
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
		
		$this['notify_to'] = json_encode($employee_ids);
		$this->save();
		
	}

	function notifyTo($employee_ids,$notification_msg,$related_document=null, $related_contact=null, $contact=null){
		if(!is_array($employee_ids)) throw $this->exception('employee_ids must be id of employees');

		if($related_document) $this['related_document_id'] = $related_document->id;
		if($related_contact) $this['related_contact_id'] = $related_contact->id;

		if(!$contact) $contact = $this->app->employee->id;
		if(!$this['contact_id']) $this['contact_id'] = $contact->id;

		
		$this['notification'] =$notification_msg;
		$this['notify_to'] = json_encode($employee_ids);
		$this->save();
	}

	function getPost($employee_id){
		if(!isset($this->emp_posts[$employee_id])){
			return $this->emp_posts[$employee_id] = $this->app->employee->newInstance()->load($employee_id)->ref('post_id');
		}

		return $this->emp_posts[$employee_id];
	}
}
