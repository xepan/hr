<?php

namespace xepan\hr;
	
class page_affiliate extends \xepan\base\Page{
	public $title = "Affiliate";
	function init(){
		parent::init();

		$affiliate = $this->add('xepan\hr\Model_Affiliate');
		if($status = $this->app->stickyGET('status'))
			$affiliate->addCondition('status',$status);
		$affiliate->add('xepan\base\Controller_TopBarStatusFilter');

		$crud = $this->add('xepan\hr\CRUD',['action_page'=>'xepan_hr_affiliatedetails'],null,['view/affiliate/affiliate-grid']);
		$crud->setModel($affiliate);
		$crud->grid->addPaginator(50);
		$crud->add('xepan\base\Controller_Avatar');
		$crud->add('xepan\base\Controller_MultiDelete');
		
		$frm=$crud->grid->addQuickSearch(['name','organization']);

		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-affiliate')->univ()->frameURL('Affiliate Details',[$this->api->url('xepan_hr_affiliatedetails'),'contact_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}

		$crud->grid->addSno();

	}
}