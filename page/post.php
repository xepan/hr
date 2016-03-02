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

class page_post extends \Page {
	public $title='Post';

	function init(){
		parent::init();

		$this->api->stickyGET('department_id');

		$post=$this->add('xepan\hr\Model_Post');
		
		$post->addExpression('parent')->set('"ToDo"');
		// 	$m->add('xepan\hr\Model_Post',['table_alias'=>'pp']);
		// 	$p_j=$m->join('post','id');
		// 	$p_j->addField('xyz','parent_post_id');
		// 	$m->addCondition('xyz',$q->getField('id'));
		// 	return $m->fieldQuery('name');

		// });


		if($_GET['department_id']){
			$post->addCondition('department_id',$_GET['department_id']);
		}

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/post/post-grid']);

		$crud->setModel($post);
		$f=$crud->grid->addQuickSearch(['name']);

		$d_f =$f->addField('DropDown','department_id')->setEmptyText("Select Department");
		$d_f->setModel('xepan\hr\Department');
		$d_f->js('change',$f->js()->submit());

		$s_f=$f->addField('DropDown','status')->setValueList(['Active'=>'Active','Inactive'=>'Inactive'])->setEmptyText('Status');
		$s_f->js('change',$f->js()->submit());

		$f->addHook('appyFilter',function($f,$m){
			if($f['department_id'])
				$m->addCondition('department_id',$f['department_id']);
			
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
