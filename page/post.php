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

class page_post extends \xepan\base\Page {
	public $title='Post';

	function init(){
		parent::init();

		$this->api->stickyGET('department_id');

		$vp = $this->add('VirtualPage');

		$post = $this->add('xepan\hr\Model_Post');
		$post->getElement('name')->caption('Post');
		$post->getElement('parent_post')->caption('Report To');
		$post->getElement('in_time')->caption('Schedule Time');
		$post->getElement('employee_count')->caption('Employee');

		$post->add('xepan\base\Controller_TopBarStatusFilter');
		if($status = $this->api->stickyGET('status'))
			$post->addCondition('status',$status);

		$post->addExpression('existing_permitted_emails')->set(function($m,$q){
			$x = $m->add('xepan\hr\Model_Post_Email_Association',['table_alias'=>'emails_str']);
			return $x->addCondition('post_id',$q->getField('id'))->_dsql()->del('fields')->field($q->expr('group_concat([0])',[$x->getElement('emailsetting_id')]));
		})->caption('E-Mail');

		if($_GET['department_id']){
			$post->addCondition('department_id',$_GET['department_id']);
		}

		$post->setOrder('order');

		$crud=$this->add('xepan\hr\CRUD');
		$crud->grid->addPaginator(50);

		$crud->form->add('xepan\base\Controller_FLC')
			->addContentSpot()
			->layout([
				'name'=>'Post~c1~4~||Intro text here',
				'department_id~Department'=>'c2~4',
				'parent_post_id~Report To'=>'c3~4',
				'salary_template_id~Salary Template'=>'c4~4',
				'leave_template_id~Leave Template'=>'c5~4',
				'order'=>'o1~4',
				'in_time'=>'Schedule~c1~6',
				'out_time'=>'c2~6',
				'permission_level'=>'Permission~c1~6~Graph/Report/Activity Accessibility Permission',
				'finacial_permit_limit'=>'c2~6~not implemented yet! credit of amount given to post',
				'allowed_menus'=>'c3~12~Menus visible to this post, leave blank for default one'
			]);

		$crud->setModel($post,
				['name','order','department_id','department','parent_post_id','parent_post','salary_template_id','leave_template_id','permission_level','in_time','out_time','finacial_permit_limit','allowed_menus'],
				['name','order','department','parent_post','in_time','out_time','employee_count','existing_permitted_emails','allowed_menus']
			);
		$crud->add('xepan\base\Controller_MultiDelete');

		if($crud->isEditing()){
			$crud->form->getElement('in_time')
					   ->setOption('showMeridian',false)
					   ->setOption('minuteStep',5)
					   ->setOption('showSeconds',false);

			$crud->form->getElement('out_time')
					   ->setOption('showMeridian',false)
					   ->setOption('minuteStep',5)
					   ->setOption('showSeconds',false);

			$model = $this->add("xepan\base\Model_Config_Menus");
			$model->tryLoadAny();

			$menu_names=[];
			foreach ($model as $m) {
				$menu_names[] = $m['name'];
			}

			$crud->form->getElement('allowed_menus')
						->enableMultiSelect()
						->setValueList(array_combine($menu_names, $menu_names))
						->setEmptyText('Default');
			if($crud->isEditing('edit')){
				$crud->form->getElement('allowed_menus')
							->set(explode(",",$crud->form->model['allowed_menus']));
			}
		}

		if(!$crud->isEditing()){

			$crud->grid->controller->importField('department_id');
			
			$f=$crud->grid->addQuickSearch(['name']);

			$d_f =$f->addField('DropDown','department_id')->setEmptyText("All Department");
			$d_f->setModel('xepan\hr\Department');
			$d_f->js('change',$f->js()->submit());

			$epan_emails = $this->add('xepan\communication\Model_Communication_EmailSetting');
			$value =[];
			foreach ($epan_emails as $ee) {
				$value[]=['value'=>$ee->id,'text'=>$ee['name']];
			}

			$crud->grid->js(true)->_load('bootstrap-editable.min')->_css('libs/bootstrap-editable')->_selector('.emails-accesible')->editable(
				[
				'url'=>$vp->getURL(),
				'limit'=> 3,
				'source'=> $value,
				'disabled'=>true
				]);
		
		}

		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-post-employees')->univ()->frameURL('Post Employees',[$this->api->url('xepan_hr_employee'),'post_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}

		
		$crud->grid->addFormatter('existing_permitted_emails','template')
					->setTemplate('<a data-type="checklist" data-value="{$existing_permitted_emails}" data-title="Permitted Emails" data-pk="{$id}" style="cursor:pointer; cursor: hand;" class="emails-accesible"></a>','existing_permitted_emails');
		$crud->grid->addFormatter('in_time','template')
					->setTemplate('<small>In_Time : {$in_time}</small><div class="xepan-push-small"></div><small>Out_Time : {$out_time}</small>','in_time');
		$crud->grid->addFormatter('employee_count','template')
					->setTemplate('<a href="#" data-id="{$id}" class="do-view-post-employees"> {$employee_count}</a>','employee_count');
		$crud->grid->addSno();
		$crud->noAttachment();
		$crud->grid->removeColumn('out_time');
		$crud->grid->removeColumn('department_id');

		$crud->addIntro([
			'name'=>'Post name',
			'order'=>'Order for showing in this grid, Ascending order',
			'department'=>'From which department this post belongs to, Always Create Departments first ',
			'parent_post'=>'Parent post, usually this hirarcy defined when seeing reports, if a post is allowed to see report from all subordinates this hirarcy comes in effect',
			'in_time'=>'Default in and out time for this post, This will automatically applies to employees, You can override this timing on each employee',
			'employee_count'=>'Number of employees under this post, click on the number to see the list',
			'existing_permitted_emails'=>'Permitted Email Accounts for this Post, Any employee under this post can see and send emails from these email accounts, you can configure email accounts from "Configuration Mode => System => Email Settings"',
			'action'=>'Current Status of post, With the help of small dropdown nearby, you can perform various actions PERMITTED TO YOU BY ACL',
			'add_button'=>'Add a new Post',
			$crud->grid->name.'_acl'=>'If you are a super user, you will see ACL button, this will let you define what Actions a post can do on any thing on a given status'
		]);
	}

}
