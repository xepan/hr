<?php

namespace xepan\hr;

class page_structurechart extends \xepan\base\Page{
	public $title = "Organization Structure Chart";

	function init(){
		parent::init();

		$company_m = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'company_name'=>"Line",
								'company_owner'=>"Line",
								'mobile_no'=>"Line",
								'company_email'=>"Line",
								'company_address'=>"Line",
								'company_pin_code'=>"Line",
								'company_description'=>"xepan\base\RichText",
								'company_logo_absolute_url'=>"Line",
								'company_twitter_url'=>"Line",
								'company_facebook_url'=>"Line",
								'company_google_url'=>"Line",
								'company_linkedin_url'=>"Line",
								],
					'config_key'=>'COMPANY_AND_OWNER_INFORMATION',
					'application'=>'communication'
				]);
		
		$company_m->add('xepan\hr\Controller_ACL');
		$company_m->tryLoadAny();

		$this->template->trySet('dept-url',$this->app->url('xepan_hr_department'));
		$this->js(true)
				->_load('mindchart/jquery.orgchart')
				->_load('mindchart/mindchart');
		$this->js(true)->_css('mindchart/jquery.orgchart');

		$department = $this->add('xepan\hr\Model_Department');

		$data[] = [
					"id"=>1,
					"name"=>$company_m['company_name'],
					"parent"=>0,
					"level"=>1,
					"type"=>'Company'
				];

		// $count = 2;
		foreach ($department as $key => $dept) {
			$data[] = [
						"id"=>$dept->id,
						"name"=>$dept['name'],
						"parent"=>1,
						"level"=>1,
						"type"=>'Department'
					];

			$post = $this->add('xepan\hr\Model_post')->addCondition('department_id',$dept->id);

			foreach ($post as $p) {
				$data[] = [
						"id"=>$p->id,
						"name"=>$p['name'],
						"parent"=>$dept->id,
						"level"=>1,
						"type"=>'Post'
					];
				
				$employee = $this->add('xepan\hr\Model_Employee');
				$employee->addCondition('post_id',$p->id);
				
				$emp_count = $p->id;
				foreach ($employee as $emp) {
					$data[] = [
						"id"=>$emp->id,
						"name"=>$emp['name'],
						"parent"=>$emp_count,
						"level"=>2,
						"type"=>'Employee'
					];

					$emp_count = $emp->id;
				}
			}
		}

		$hierarchy_view = $this->add('View',null,'chart')->setStyle('overflow','scroll');
		$hierarchy_view->js(true)->xepan_mindchart(
									[	
										"data" => $data,
										"allowEdit"=>false,
										"showControls"=>false
									]);
	}

	function defaultTemplate(){
		return['page\structurechart'];
	}
}