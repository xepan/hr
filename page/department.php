<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\hr;

class page_department extends \Page {
	public $title='Department';

	function init(){
		parent::init();
		
		$department=$this->add('xepan\hr\Model_Department');

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/department/department-grid']);

		$crud->setModel($department);
		$f=$crud->grid->addQuickSearch(['name']);

		$s_f=$f->addField('DropDown','status')->setValueList(['Active'=>'Active','Inactive'=>'Inactive'])->setEmptyText('Status');
		$s_f->js('change',$f->js()->submit());

		$f->addHook('appyFilter',function($f,$m){
			if($f['status']='Active'){
				throw new \Exception("Active", 1);
				$m->addCondition('status','Active');
			}else{
				throw new \Exception("Inactive", 1);
				$m->addCondition('status','Inactive');

			}

		});

		
		
	}
}
