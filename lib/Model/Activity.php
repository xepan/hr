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
		if($document)
			$acl_m->addCondition('document_type',$document['docuement_type']);
		else
			$acl_m->addCondition('document_type',$this->ref('related_document_id')->get('docuement_type'));

		if(!is_array($list_of_actions)) $list_of_actions = explode(",", $list_of_actions);

		$employee_ids=[];
		foreach ($acl_m as $acl) {
			$actions = $acl['action_allowed'][$current_status];
			foreach ($list_of_actions as $req_act) {
				$text_code = $actions[$req_act]; // Self Only, All, None, Etc.
				switch ($text_code) {
					case 'Self Only':
						# code...
						# if($document->created_by->post_id == $acl->post_id) include this id
						if($document->ref('created_by_id')->get('post_id') == $acl['post_id'])
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
}
