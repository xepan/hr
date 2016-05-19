<?php

namespace xepan\hr;
	
class page_affiliate extends \xepan\base\Page{
	public $title = "Affiliate";
	function init(){
		parent::init();

		$affiliate = $this->add('xepan\hr\Model_Affiliate');
		if($status = $this->app->stickyGET('status'))
			$affiliate->addCondition('status',$status);
		$affiliate->add('xepan\hr\Controller_SideBarStatusFilter');

		$crud = $this->add('xepan\hr\CRUD',['action_page'=>'xepan_hr_affiliatedetails'],null,['view/affiliate/affiliate-grid']);
		$crud->setModel($affiliate);
		$crud->grid->addPaginator(50);
		$crud->add('xepan\base\Controller_Avatar');
		
		$frm=$crud->grid->addQuickSearch(['name']);

	}
}