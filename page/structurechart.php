<?php
/*************************************************************************************
    TODO
    a. Post should be added in array based upon parent and child hierarchy
    b. Employees should be added one under another to reduce horizontal overflowing
    c. Seperate fill colors for boxes [dept, post, emp] to maintain distinction
    d. Level open and close mechanism to reduce query timing
    							OR
    e. Saving data array in configuration to reduce query timing
**************************************************************************************/
namespace xepan\hr;

class page_structurechart extends \xepan\base\Page{
	public $title = "Organization Structure Chart";

	function init(){
		parent::init();


		/********************************************************
			LOADING JQUERY AND CSS OF MIND CHART
		********************************************************/
		$this->js(true)
				->_load('mindchart/jquery.orgchart')
				->_load('mindchart/mindchart');
		$this->js(true)->_css('mindchart/jquery.orgchart');

		/********************************************************
			LOADING JSON MODEL TO FIND ORGAIZATION NAME
		********************************************************/
		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		$company_m->tryLoadAny();

		// SETTING DEPARTMENT PAGE URL ON BUTTON TO NAVIAGATE TO DEPARTMET GRID
		$this->template->trySet('dept-url',$this->app->url('xepan_hr_department'));

		// ROOT NODE SHOWING ORGANIZATION NAME
		$data[] = ["id"=>1,"name"=>$company_m['company_name'],"parent"=>0,"level"=>1,"type"=>'Company'];


		/********************************************************
			SETTING VALUES IN DATA ARRAY [DEPT, POST, EMP]	
		********************************************************/
		$department = $this->add('xepan\hr\Model_Department');
		foreach ($department as $dept) {
			$data = $this->addDepartmentInArray($dept,$data);
			$post = $this->add('xepan\hr\Model_post');
			$post->addCondition('department_id',$dept->id);			
			foreach ($post as $p) {
				$data = $this->addPostInArray($p,$dept,$data);
				$employee = $this->add('xepan\hr\Model_Employee');
				$employee->addCondition('post_id',$p->id);
				foreach ($employee as $emp) {
					$data = $this->addEmployeeToArray($emp,$p,$data);
				}
			}
		}

		/********************************************************
			MINDCHART IN ACTION [PASSIG VALUES AND DATA ARRAY] 
		********************************************************/
		$hierarchy_view = $this->add('View',null,'chart')->setStyle('overflow','scroll');
		$hierarchy_view->js(true)->xepan_mindchart(["data" => $data,"allowEdit"=>false,"showControls"=>false]);
	}


	/***********************************************************
		FUNCTION TO RETURN DATA ARRAY AFTER ADDING DEPARTMENTS
	************************************************************/
	function addDepartmentInArray($dept, $data){
		$data[] = ["id"=>$dept->id,"name"=>$dept['name'],"parent"=>1,"level"=>1,"type"=>'Department'];
		return $data;			
	}

	/***********************************************************
		FUNCTION TO RETURN DATA ARRAY AFTER ADDING POSTS
	************************************************************/
	function addPostInArray($p,$dept,$data){
		$data[] = ["id"=>$p->id,"name"=>$p['name'],"parent"=>$dept->id,"level"=>1,"type"=>'Post'];
		return $data;			
	}

	/***********************************************************
		FUNCTION TO RETURN DATA ARRAY AFTER ADDING EMPLOYEES
	************************************************************/
	function addEmployeeToArray($emp,$p,$data){
		$data[] = ["id"=>$emp->id,"name"=>$emp['name'],"parent"=>$p->id,"level"=>2,"type"=>'Employee'];
		return $data;
	}

	function defaultTemplate(){
		return['page\structurechart'];
	}
}