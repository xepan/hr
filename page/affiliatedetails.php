<?php

namespace xepan\hr;

class page_affiliatedetails extends \xepan\base\Page {
	public $title ='Affiliate Details';
	public $breadcrumb=['Home'=>'index','Affiliate'=>'xepan_hr_affiliate','Details'=>'#'];


	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';
		$affiliate= $this->add('xepan\hr\Model_Affiliate')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		
		if($action=="add"){

			$affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view_full_width');
			$affiliate_view->document_view->effective_template->del('im_and_events_andrelation');
			$affiliate_view->document_view->effective_template->del('email_and_phone');
			$affiliate_view->document_view->effective_template->del('avatar_wrapper');
			$affiliate_view->document_view->effective_template->del('contact_since_wrapper');
			$affiliate_view->document_view->effective_template->del('send_email_sms_wrapper');
			$affiliate_view->document_view->effective_template->del('online_status_wrapper');
			$affiliate_view->document_view->effective_template->del('contact_type_wrapper');
			$this->template->del('details');
			$affiliate_view->setStyle(['width'=>'50%','margin'=>'auto']);
		}else{
			$affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_Employee','view_document_class'=>'xepan\hr\View_Document'],'contact_view');
		}	

		$affiliate_view->setModel($affiliate);

		$detail = $this->add('xepan\hr\View_Document',['action'=> $action,'id_field_on_reload'=>'contact_id'],'details',['view/affiliate/details']);
		$detail->setModel($affiliate,['narration'],['narration']);

	}

	function defaultTemplate(){
		return ['page/affiliateprofile'];
	}
}
