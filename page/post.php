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

		$vp = $this->add('VirtualPage');
		$vp->set(function($p){
			try{
				$post = $this->add('xepan\hr\Model_Post')->load($_POST['pk']);
				$post->ref('EmailPermissions')->deleteAll();
				foreach ($_POST['value']?:[] as $emailsetting_id) {
					$this->add('xepan\hr\Model_Post_Email_Association')
						->set('post_id',$_POST['pk'])
						->set('emailsetting_id',$emailsetting_id)
						->saveAndUnload();
				}
			}catch(\Exception $e){
				http_response_code(400);
				echo $e->getMessage();
			}
			exit;
			
		});

		$post=$this->add('xepan\hr\Model_Post');
		$post->addExpression('existing_permitted_emails')->set(function($m,$q){
			$x = $m->add('xepan\hr\Model_Post_Email_Association',['table_alias'=>'emails_str']);
			return $x->addCondition('post_id',$q->getField('id'))->_dsql()->del('fields')->field($q->expr('group_concat([0])',[$x->getElement('emailsetting_id')]));
		});

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

		$epan_emails = $this->add('xepan\base\Model_Epan_EmailSetting');
		$value =[];
		foreach ($epan_emails as $ee) {
			$value[]=['value'=>$ee->id,'text'=>$ee['email_username']];
		}

		$crud->grid->js(true)->_selector('.emails-accesible')->editable(
			[
			'url'=>$vp->getURL(),
			'limit'=> 3,
			'source'=> $value
			]);
		
	}

}
