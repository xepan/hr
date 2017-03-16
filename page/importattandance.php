<?php

namespace xepan\hr;

class page_importattandance extends \xepan\base\Page{
	public $title = "Import Attendance";
	function init(){
		parent::init();
		
		$post_id = $this->app->stickyGET('post_id');

		// $save = $day_form->addSubmit('Import')->addClass('btn btn-primary');
		
		/*=========== Days ===============*/
		$day_attendance_form = $this->add('Form',null,'day');
		
		$department = $day_attendance_form->addField('xepan\base\DropDownNormal','department')->setEmptyText('Please Select Department');
		$department->setModel('xepan\hr\Department');
		
		$post = $day_attendance_form->addField('xepan\base\DropDownNormal','post')->setEmptyText('Please Select Post');
		$post->setModel('xepan\hr\Post');

		$dept_field = $day_attendance_form->getElement('department');
		$post_field = $day_attendance_form->getElement('post');

		if($dept_id = $this->app->stickyGET('dept')){			
			$post_mdl = $post_field->getModel()->addCondition('department_id',$dept_id);
		}
		$dept_field->js('change',$post_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$post_field->name]),'dept'=>$dept_field->js()->val()]));


		$download_btn = $day_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($_GET['download_sample_csv_file']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active')
						    ->addCondition('post_id',$post_id);
						
			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post']];
			}

			$header = ['id','name','department','post','is_present'];

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import.csv\"");
		 //    header("Content-Length: " . strlen($output));
		 //    header("Content-Transfer-Encoding: binary");
		 //    print $output;
		    exit;

		}

		if($day_attendance_form->isSubmitted()){
			$day_attendance_form->js()->univ()->newWindow($day_attendance_form->app->url('xepan_hr_importattandance',['download_sample_csv_file'=>true,'post_id'=>$day_attendance_form['post']]))->execute();
		}


		/*=========== Week ===============*/
		$week_attendance_form = $this->add('Form',null,'week');
		
		if($_GET['download_sample_csv_file']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active')
						    ->addCondition('post_id',$post_id);
						
			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post']];
			}

			$header = ['id','name','department','post','is_present'];

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import.csv\"");
		 //    header("Content-Length: " . strlen($output));
		 //    header("Content-Transfer-Encoding: binary");
		 //    print $output;
		    exit;

		}

		$download_btn = $week_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($week_attendance_form->isSubmitted()){
			$week_attendance_form->js()->univ()->newWindow($week_attendance_form->app->url('xepan_hr_importattandance',['download_sample_csv_file'=>true,'post_id'=>$week_attendance_form['post']]))->execute();
		}

		/*=========== Month ===============*/
		$month_attendance_form = $this->add('Form',null,'month');
		
		if($_GET['download_sample_csv_file']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active')
						    ->addCondition('post_id',$post_id);
						
			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post']];
			}

			$header = ['id','name','department','post','is_present'];

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import.csv\"");
		 //    header("Content-Length: " . strlen($output));
		 //    header("Content-Transfer-Encoding: binary");
		 //    print $output;
		    exit;

		}

		$download_btn = $month_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($month_attendance_form->isSubmitted()){
		
		}
		
	}

	function defaultTemplate(){
		return['view\employee\importattandance'];
	}
}