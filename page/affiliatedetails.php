<?php

namespace xepan\hr;

class page_affiliatedetails extends \xepan\base\Page {
	public $title ='Affiliate Details';
	public $breadcrumb=['Home'=>'index','Affiliate'=>'xepan_hr_affiliate','Details'=>'#'];


	function init(){
		parent::init();

		$action = $this->api->stickyGET('action')?:'view';
		$affiliate= $this->add('xepan\hr\Model_Affiliate')->tryLoadBy('id',$this->api->stickyGET('contact_id'));
		$affiliate_view = $this->add('xepan\base\View_Contact',['acl'=>'xepan\hr\Model_affiliate'],'contact_view');
		$affiliate_view->setModel($affiliate);

		$detail = $this->add('xepan\hr\View_Document',['action'=> $action,'id_field_on_reload'=>'contact_id'],'details',['view/affiliate/details']);
		$detail->setModel($affiliate,['narration'],['narration']);

	}

	function defaultTemplate(){
		return ['page/affiliateprofile'];
	}
}
