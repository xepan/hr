<?php

namespace xepan\hr;

class page_importattandance extends \xepan\base\Page{
	public $title = "Import Attendance";
	public $days_array = [];

	function init(){
		parent::init();
		
		$department_id = $this->app->stickyGET('department_id');
		$post_id = $this->app->stickyGET('post_id');

		$tabs = $this->add('Tabs');
		$day_tab = $tabs->addTab('Days');

		/**
		=========== Days ===============
		*/
		$col = $day_tab->add('Columns')->addClass('row');
		$left_col = $col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$import_column  = $left_col->add('View')->addClass('well well-sm');

		$import_column->add('View')->setElement('h3')->set('Import CSV File');

		$right_col = $col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$download_column  = $right_col->add('View')->addClass('well well-sm');
		$download_column->add('View')->setElement('h3')->set('Download Sample CSV File');

		$day_attendance_form = $download_column->add('Form');

		$dept_field = $day_attendance_form->addField('xepan\base\DropDownNormal','department')->setEmptyText('All Department');
		$dept_field->setModel('xepan\hr\Department');
		
		$post_field = $day_attendance_form->addField('xepan\base\DropDownNormal','post')->setEmptyText('All Post');
		$post_field->setModel('xepan\hr\Post');

		if($dept_id = $this->app->stickyGET('dept')){			
			$post_mdl = $post_field->getModel()->addCondition('department_id',$dept_id);
		}
		
		$dept_field->js('change',$post_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$post_field->name]),'dept'=>$dept_field->js()->val()]));

		$download_btn = $day_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($_GET['download_sample_csv_file']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active');
			if($department_id && (!$post_id) )
				$emp_mdl->addCondition('department_id',$department_id);
			
			if($post_id)
				$emp_mdl->addCondition('post_id',$post_id);

			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post'],'present_type'=>$mdl['salary_payment_type']];
			}

			$header = ['id','name','department','post','working_type_unit','unit_count'];

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import.csv\"");
		    exit;
		}

		if($day_attendance_form->isSubmitted()){
			if($day_attendance_form->isClicked($download_btn)){
				$day_attendance_form->js()->univ()->newWindow($day_attendance_form->app->url('xepan_hr_importattandance',['download_sample_csv_file'=>true,'department_id'=>$day_attendance_form['department'],'post_id'=>$day_attendance_form['post']]))->execute();
			}
		}

		// day attandance importer
		$day_importer_form = $import_column->add('Form');
		$day_importer_form->addField('DatePicker','date');
		$day_importer_form->addField('Upload','day_attendance_csv_file')->setModel('xepan\filestore\File');
		$import_btn_for_day = $day_importer_form->addSubmit('Import Attendance')->addClass('btn btn-primary');
		
		if($day_importer_form->isSubmitted()){
			
			$file_m = $this->add('xepan\filestore\Model_File')->load($day_importer_form['day_attendance_csv_file']);
			$path = $file_m->getPath();		

			$importer = new \xepan\base\CSVImporter($path,true,',');
			$csv_data = $importer->get();
			$date = $day_importer_form['date'];
			
			// data of present day only
			$present_emp_list = [];
			foreach ($csv_data as $key => $emp_attandance) {
				if(!$emp_attandance['unit_count']) continue;
				
				$present_emp_list[$emp_attandance['id']] = [
														$date=>[
															'working_type_unit'=>$emp_attandance['working_type_unit'],
															'unit_count'=>$emp_attandance['unit_count']
															]
													];
			}

			$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
			

			$attendance_m->insertAttendanceFromCSV($present_emp_list);
			$day_importer_form->js()->univ()->successMessage('Done')->execute();
		}


		/**
		
		=========== Week ===============
		
		*/

		$week_tab = $tabs->addTab('Week');

		$week_col = $week_tab->add('Columns')->addClass('row');
		$left_colm = $week_col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$import_colum  = $left_colm->add('View')->addClass('well well-sm');

		$import_colum->add('View')->setElement('h3')->set('Import CSV File');

		$right_colm = $week_col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$download_colum  = $right_colm->add('View')->addClass('well well-sm');
		$download_colum->add('View')->setElement('h3')->set('Download Sample CSV File');

		$week_attendance_form = $download_colum->add('Form');

		$dept_field = $week_attendance_form->addField('xepan\base\DropDownNormal','department')->setEmptyText('All Department');
		$dept_field->setModel('xepan\hr\Department');
		
		$post_field = $week_attendance_form->addField('xepan\base\DropDownNormal','post')->setEmptyText('All Post');
		$post_field->setModel('xepan\hr\Post');

		if($dept_id = $this->app->stickyGET('dept')){			
			$post_mdl = $post_field->getModel()->addCondition('department_id',$dept_id);
		}
		
		$dept_field->js('change',$post_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$post_field->name]),'dept'=>$dept_field->js()->val()]));

		$download_btn = $week_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($_GET['download_sample_csv_file_for_week']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active');

			if($department_id && (!$post_id) )
				$emp_mdl->addCondition('department_id',$department_id);
			
			if($post_id)
				$emp_mdl->addCondition('post_id',$post_id);

			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post'],'present_type'=>$mdl['salary_payment_type']];
			}

			$this->getWorkingDays();

			$header = ['id','name','department','post','working_type_unit'];

			//merging array of days and header
			$header = array_merge($header,$this->days_array);

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import_for_week.csv\"");
		    exit;
		}

		if($week_attendance_form->isSubmitted()){
			if($week_attendance_form->isClicked($download_btn)){
				$week_attendance_form->js()->univ()->newWindow($week_attendance_form->app->url('xepan_hr_importattandance',['download_sample_csv_file_for_week'=>true,'department_id'=>$week_attendance_form['department'],'post_id'=>$week_attendance_form['post']]))->execute();
			}
		}

		$week_importer_form = $import_colum->add('Form');

		$date_field = $week_importer_form->addField('DatePicker','date');
		$week_importer_form->addField('Upload','day_attendance_csv_file')->setModel('xepan\filestore\File');
		$week_importer_form->addSubmit('Import Attendance')->addClass('btn btn-primary');

		if($week_importer_form->isSubmitted()){			
			$starting_day = date('D', strtotime($week_importer_form['date']));
			
			if($starting_day != 'Mon')
				$week_importer_form->displayError('date','Date should fall on monday, currently selected :'.$starting_day);	

			$this->getWorkingDays();
			$date_array = [];	
			for ($i=0; $i <7 ; $i++) { 
				$new_date = date("Y-m-d", strtotime('+ '.$i.' day', strtotime($week_importer_form['date'])));
				$datesday = strtolower(date('l', strtotime($new_date)));
				if(in_array($datesday, $this->days_array))
					$date_array[] = $new_date;						
			}

			$file_m = $this->add('xepan\filestore\Model_File')->load($week_importer_form['day_attendance_csv_file']);
			$path = $file_m->getPath();		

			$importer = new \xepan\base\CSVImporter($path,true,',');
			$csv_data = $importer->get();



			// $count = 0;
			// $outer_count = 0;
			// foreach ($csv_data as $key => $value){
			// 	foreach ($value as $key1 => $value1){
			// 		if($key1 ==='id' || $key1 ==='name' || $key1 ==='post' || $key1 ==='department' || $key1 ==='working_type_unit')
			// 			continue;

			// 		unset($csv_data[$outer_count] [$key1]); 				
			// 		$csv_data[$outer_count] [$date_array[$count]]= ['unit_count'=>$value1]; 
			// 		$count++;
			// 	}
			// 	$count = 0;
			// 	$outer_count++;
			// }

			// echo "<pre>";
			// print_r($csv_data);
			// echo "</pre>";
			// exit;

			$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
			$attendance_m->insertAttendanceFromCSV($present_emp_list);
			$week_importer_form->js()->univ()->successMessage('Done')->execute();
		}

		/**
		
		=========== Month ===============
		
		*/

		$month_tab = $tabs->addTab('Month');

		$month_col = $month_tab->add('Columns')->addClass('row');
		$left_colmn = $month_col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$import_colmn  = $left_colmn->add('View')->addClass('well well-sm');

		$import_colmn->add('View')->setElement('h3')->set('Import CSV File');

		$right_colmn = $month_col->addColumn('6')->addClass('col-md-6 col-sm-6 col-xs-6 col-lg-6');
		$download_colmn  = $right_colmn->add('View')->addClass('well well-sm');
		$download_colmn->add('View')->setElement('h3')->set('Download Sample CSV File');

		$month_attendance_form = $download_colmn->add('Form');

		$dept_field = $month_attendance_form->addField('xepan\base\DropDownNormal','department')->setEmptyText('All Department');
		$dept_field->setModel('xepan\hr\Department');
		
		$post_field = $month_attendance_form->addField('xepan\base\DropDownNormal','post')->setEmptyText('All Post');
		$post_field->setModel('xepan\hr\Post');

		if($dept_id = $this->app->stickyGET('dept')){			
			$post_mdl = $post_field->getModel()->addCondition('department_id',$dept_id);
		}
		
		$dept_field->js('change',$post_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$post_field->name]),'dept'=>$dept_field->js()->val()]));

		$download_btn = $month_attendance_form->addSubmit('Download Sample File')->addClass('btn btn-primary');

		if($_GET['download_sample_csv_file_for_month']){
			$emp_mdl = $this->add('xepan\hr\Model_Employee_Active');

			if($department_id && (!$post_id) )
				$emp_mdl->addCondition('department_id',$department_id);
			
			if($post_id)
				$emp_mdl->addCondition('post_id',$post_id);

			$emp_arr = [];	
			foreach ($emp_mdl as $mdl) {
				$emp_arr [] = ['id'=>$mdl['id'],'name'=>$mdl['name'],'department'=>$mdl['department'],'post'=>$mdl['post'],'present_type'=>$mdl['salary_payment_type']];
			}

		    $dates = [];
		    $current = '1';
		    $last = '31';
		    
		    while($current <= $last ) { 
	            $dates[] = $current;
		        $current += 1;
		    }

		    $header = ['id','name','department','post','working_type_unit'];

			//merging array of days and header
			$header = array_merge($header,$dates);

		    $fp = fopen("php://output", "w");
		    fputcsv ($fp, $header, "\t");
		    foreach($emp_arr as $row){
		        fputcsv($fp, $row, "\t");
		    }
		    fclose($fp);
			header("Content-type: text/csv");
		    header("Content-disposition: attachment; filename=\"sample_xepan_attandance_import_for_month.csv\"");
		    exit;
		}

		if($month_attendance_form->isSubmitted()){
			if($month_attendance_form->isClicked($download_btn)){
				$month_attendance_form->js()->univ()->newWindow($month_attendance_form->app->url('xepan_hr_importattandance',['download_sample_csv_file_for_month'=>true,'department_id'=>$month_attendance_form['department'],'post_id'=>$month_attendance_form['post']]))->execute();
			}
		}

		$month_importer_form = $import_colmn->add('Form');

		$month_importer_form->addField('DatePicker','date');
		$month_importer_form->addField('Upload','day_attendance_csv_file')->setModel('xepan\filestore\File');
		$month_importer_form->addSubmit('Import Attendance')->addClass('btn btn-primary');
		if($month_importer_form->isSubmitted()){			

			$file_m = $this->add('xepan\filestore\Model_File')->load($month_importer_form['day_attendance_csv_file']);
			$path = $file_m->getPath();		

			$importer = new \xepan\base\CSVImporter($path,true,',');
			$csv_data = $importer->get();

			echo "<pre>";
			print_r($csv_data);
			echo "</pre>";
			exit;

			$attendance_m = $this->add('xepan\hr\Model_Employee_Attandance');
			$attendance_m->insertAttendanceFromCSV($present_emp_list);
			$month_importer_form->js()->univ()->successMessage('Done')->execute();
		}

	}	

	function getWorkingDays(){
		$week_day_model = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'monday'=>"checkbox",
							'tuesday'=>"checkbox",
							'wednesday'=>"checkbox",
							'thursday'=>"checkbox",
							'friday'=>"checkbox",
							'saturday'=>"checkbox",
							'sunday'=>"checkbox"
							],
				'config_key'=>'HR_WORKING_WEEK_DAY',
				'application'=>'hr'
			]);
		$week_day_model->tryLoadAny();

		$temp = [];
		$temp = $week_day_model->get();

		foreach ($temp as $key => $value) {
			if(!$value)
				continue;
			$this->days_array [] = $key;	
		}
	}
}