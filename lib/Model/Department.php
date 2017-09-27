<?php

namespace xepan\hr;

class Model_Department extends \xepan\hr\Model_Document{

	public $status=['Active','InActive'];
	
	public $actions = [
		'Active'=>['view','edit','delete','deactivate'],
		'InActive' => ['view','edit','delete','activate']
	];

	function init(){
		parent::init();

		$dep_j = $this->join('department.document_id');
		$dep_j->addField('name')->sortable(true);
		$dep_j->addField('production_level')->sortable(true);

		$dep_j->hasMany('xepan\hr\Post','department_id',null,'Posts');
		$dep_j->hasMany('xepan\hr\Employee','department_id',null,'Employees');
		$dep_j->addField('is_system')->type('boolean')->defaultValue(false)->system(true);
		$dep_j->addField('is_outsourced')->type('boolean')->defaultValue(false);
		$dep_j->addField('simultaneous_no_process_allowed')->hint('No. Of Machine Allowed To be Used In Production For Process JobCard');;

		$this->addExpression('posts_count')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Post',['table_alias'=>'dept_post_count'])->addCondition('department_id',$m->getElement('id'))->count();
			
		})->sortable(true);

		$this->addExpression('employee_count')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Employee',['table_alias'=>'dept_emp_count'])->addCondition('department_id',$m->getElement('id'))->count();
			
		})->sortable(true);

		$this->addExpression('active_employee_count')->set(function($m,$q){
			return $this->add('xepan\hr\Model_Employee',['table_alias'=>'dept_emp_count'])->addCondition('status','Active')->addCondition('department_id',$m->getElement('id'))->count();
			
		})->sortable(true)->caption('Employee Count');

		$this->getElement('status')->defaultValue('Active');
		$this->addCondition('type','Department');

		$this->addHook('beforeDelete',[$this,'checkForPostsAndEmployees']);
		$this->addHook('beforeSave',[$this,'updateSearchString']);
		
		$this->is([
				'name|unique_in_epan|to_trim|required',
				'production_level|required?Production Level must be filled|int|>0'
			]);
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Department : '".$this['name']."' Acivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('deactivate','Active',$this);
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Department : '".$this['name']."'  has been deactivated", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,null)
            ->notifyWhoCan('activate','InActive',$this);
		$this->saveAndUnload();
	}

	function checkForPostsAndEmployees(){
		$posts_count=$this->ref('Posts')->count()->getOne();
		$employee_count=$this->ref('Employees')->count()->getOne();
		
		if($posts_count or $employee_count){
			throw new \Exception("Department Can not be deleted its content Post And Employee Delete First", 1);
		}
	}

	function updateSearchString($m){

		$search_string = ' ';
		$search_string .=" ". $this['name'];

		if($this->loaded()){
			$post = $this->ref('Posts');
			foreach ($post as $post_detail) 
			{
				$search_string .=" ". $post_detail['name'];
				$search_string .=" ". $post_detail['in_time'];
				$search_string .=" ". $post_detail['out_time'];
			}
		}

		if($this->loaded()){
			$employees = $this->ref('Employees');
			foreach ($employees as $employees_detail) {
				$search_string .=" ". $employees_detail['offer_date'];
				$search_string .=" ". $employees_detail['contract_date'];
				$search_string .=" ". $employees_detail['doj'];
				$search_string .=" ". $employees_detail['leaving_date'];
				$search_string .=" ". $employees_detail['mode'];
				$search_string .=" ". $employees_detail['in_time'];
				$search_string .=" ". $employees_detail['out_time'];
			}
		}

		$this['search_string'] = $search_string;
	}


	function quickSearch($app,$search_string,&$result_array,$relevency_mode){
		$this->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$this->addCondition('Relevance','>',0);
 		$this->setOrder('Relevance','Desc');
 		
 		if($this->count()->getOne()){
 			foreach ($this->getRows() as $data) {
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_hr_department',['status'=>$data['status']])->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 					'quick_info'=>'Post Count: '.$data['posts_count'],
 				];
 			}
		}
		

    	$post = $this->add('xepan\hr\Model_Post');	
     	$post->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$post->addCondition('Relevance','>',0);
 		$post->setOrder('Relevance','Desc');
 		
 		if($post->count()->getOne()){
 			foreach ($post->getRows() as $data) {
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_hr_post',['status'=>$data['status']])->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 					'quick_info'=>'Employee Count: '.$data['employee_count'].' In-Time: '.$data['in_time'].' Out-Time: '.$data['out-time'],
 				];
 			}
		}

 		$employee = $this->add('xepan\hr\Model_Employee');	
     	$employee->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$employee->addCondition('Relevance','>',0);
 		$employee->setOrder('Relevance','Desc');
 			
 		if($employee->count()->getOne()){
 			foreach ($employee->getRows() as $data) {
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_hr_employeedetail',['status'=>$data['status'],'contact_id'=>$data['id']])->getURL(),
 				];
 			}
		}

		$user = $this->add('xepan\base\Model_User');	
     	$user->addExpression('Relevance')->set('MATCH(username, type, scope) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$user->addCondition('Relevance','>',0);
 		$user->setOrder('Relevance','Desc');
 		
 		if($user->count()->getOne()){
 			foreach ($user->getRows() as $data) {
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['username'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_hr_user',['status'=>$data['status']]),
 				];
 			}
		}

 		$affiliate = $this->add('xepan\hr\Model_Affiliate');	
     	$affiliate->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$affiliate->addCondition('Relevance','>',0);
 		$affiliate->setOrder('Relevance','Desc');
 		
 		if($affiliate->count()->getOne()){
 			foreach ($affiliate->getRows() as $data) {
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan/hr/affiliatedetails',['status'=>$data['status'],'contact_id'=>$data['id']])->getURL(),
 				];
 			}
		}
	}
}