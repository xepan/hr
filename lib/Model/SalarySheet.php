<?php

namespace xepan\hr;

class Model_SalarySheet extends \xepan\hr\Model_SalaryAbstract{
	
	public $status = ['Draft','Submitted','Approved','Canceled','Redraft'];
	public $actions = [
					'Draft'=>['view','edit','delete','submit'],
					'Redraft'=>['view','edit','delete','submit'],
					'Submitted'=>['view','edit','delete','approved','canceled'],
					'Approved'=>['view','edit','delete','canceled','csvFiles'],
					'Canceled'=>['view','edit','delete','redraft']
				];
	function init(){

		parent::init();

		$this->addCondition('type','SalarySheet');

		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){
		$this->app->hook('remove_account_entry',[$this]);
	}

	function submit(){
		$this['status'] = "Submitted";
		$this->save();

		$msg = [
				'title'=>$this['name'].' Salary Sheet Submitted',
				'message'=>'Salary Sheet Submitted of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." submitted for approve ",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
	}

	function approved(){
		$this['status'] = "Approved";
		$this->save();
		
		$ss_model = $this->add('xepan\hr\Model_SalarySheet');
		$sal = $this->add('xepan\hr\Model_Salary');
		foreach ($sal->getRows() as $s) {
			$norm_name = $this->app->normalizeName($s['name']);
			$ss_model->addExpression($norm_name)->set(function($m,$q)use($s,$norm_name){
				return $q->expr('IFNULL([0],0)', [$m->refSQL('xepan\hr\EmployeeRow')->sum($norm_name)]);
			});
		}

		$ss_model->addExpression('total_amout_add')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('xepan\hr\EmployeeRow')->sum('total_amout_add')]);
		})->type('money');


		$ss_model->addExpression('total_amount_deduction')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)',[$m->refSQL('xepan\hr\EmployeeRow')->sum('total_amount_deduction')]);
		})->type('money');
		
		$ss_model->addExpression('net_amount')->set(function($m,$q){
			return $q->expr('[0]-[1]',[$m->getElement('total_amout_add'),$m->getElement('total_amount_deduction')]);
		})->type('money');

		$ss_model->load($this->id);
		

		$msg = [
				'title'=>$this['name'].' Salary Sheet Approved',
				'message'=>'Salary Sheet Approved of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		$this->app->hook('salary_sheet_approved',[$ss_model]);
		
		// PAID REIMBURSEMENT
		$this->paidReimbursement();
		$this->deductionReceived();

		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." Approved by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg); 
	}

	function canceled(){
		$this['status'] = "Canceled";
		$this->save();
		
		$msg = [
				'title'=>$this['name'].' Salary Sheet Canceled',
				'message'=>'Salary Sheet Canceled of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Salary Sheet ".$this['name']." Canceled by ".$this->app->employee['name'],null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);
		$this->app->hook('salary_sheet_canceled',[$this]);
	}

	function redraft(){
		$this['status'] = "Redraft";
		$this->save();

		$msg = [
				'title'=>"Re-Draft Salary Sheet [".$this['name']."] you submitted",
				'message'=>'Re-Draft Salary Sheet of MONTH: '.$this['month']." and YEAR: ".$this['year'],
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>null
			];
		
		$this->app->employee
	           	->addActivity("Re-Draft Salary Sheet [".$this['name']."] you submitted",null, $this['created_by_id'] /*Related Contact ID*/,null,null,null)
	            ->notifyWhoCan(null,null,false,$msg);
	}

	function paidReimbursement(){

		if(!$this->loaded()) throw new \Exception("model mst loaded", 1);

		$emp_row = $this->add('xepan\hr\Model_EmployeeRow')
				->addCondition('salary_abstract_id',$this->id);
		
		$reimbursement_amount = 0;
		foreach ($emp_row as $e_row){

			$reimbursement_amount = $e_row['reimbursement_amount'];

			$reimbursemnt_dtl = $this->add('xepan\hr\Model_ReimbursementDetail');
			$reimbursemnt_dtl->addExpression('status',function($m,$q){
				return $m->refSQL('reimbursement_id')->fieldQuery('status');
			});
			$reimbursemnt_dtl->addCondition('employee_id',$e_row['employee_id']);
			$reimbursemnt_dtl->addCondition([['status',"Approved"],['status',"PartiallyPaid"]]);
			$reimbursemnt_dtl->addCondition('due_amount','>',0);
			$reimbursemnt_dtl->setOrder('date','asc');
			
			foreach ($reimbursemnt_dtl as $r_dtl) {
				if($reimbursement_amount <= 0) break;

				// may be removed
				$temp =  $this->add('xepan\hr\Model_ReimbursementDetail')->load($r_dtl['id']);

				if($reimbursement_amount >= $temp['due_amount']){
					$temp['paid_amount'] += $temp['due_amount'];
					$reimbursement_amount -= $temp['due_amount'];
				}else{
					$temp['paid_amount'] += $reimbursement_amount;
					$reimbursement_amount -= $reimbursement_amount;
				}
				$temp->save();
			}
		}


	}

	function deductionReceived(){
		if(!$this->loaded()) throw new \Exception("model mst loaded", 1);

		$emp_row = $this->add('xepan\hr\Model_EmployeeRow')
				->addCondition('salary_abstract_id',$this->id);
		
		$deduction_amount = 0;
		foreach ($emp_row as $e_row){
			$deduction_amount = $e_row['deduction_amount'];

			$deduction_mdl = $this->add('xepan\hr\Model_Deduction')
							->addCondition('employee_id',$e_row['employee_id'])
							->addCondition([['status','Approved'],['status','PartiallyRecieved']]);
			
			foreach ($deduction_mdl as $mdl) {
				if($deduction_amount <= 0) continue;

				if($deduction_amount >= $mdl['due_amount']){
					$mdl['received_amount'] += $mdl['due_amount'];
					$deduction_amount -= $mdl['due_amount'];
					$mdl['status'] = "Recieved";
				}else{
					$mdl['received_amount'] += $deduction_amount;
					$deduction_amount -= $deduction_amount;
					$mdl['status'] = "PartiallyRecieved";
				}
					$mdl->saveAndUnload();
			}
		}
	}

	function page_csvFiles($page){

		$form = $page->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->showLables(true)
			->makePanelsCoppalsible(true)
			->addContentSpot()
			->layout([
				'salary_sheet_fields'=>'Salary Sheet Fields~c1~12~closed',
				'salary_details_fields'=>'Salary Details~c1~12~closed',
				'emp_fields'=>'Employee Details~c1~12~closed',
				'format'=>'Format to Export~c1~12',
				'download'=>'c2~4',
				'filename'=>'c3~4',
				'decimal_digits'=>'c4~2',
			]);

		$format_field = $form->addField('format');
		$form->addField('Checkbox','download');
		$form->addField('Number','decimal_digits');
		$form->addField('filename');

		// set fields
		// SalarySheet -> EmployeeRow -> xepan\hr\SalaryDetail (with each salary_id)
		// salary sheet fields

		$sheet_fields = ['name','month','year'];
		$form->layout->add('View',null,'salary_sheet_fields')->set(implode(", ",$sheet_fields));

		// salary details fields
		$row_fields = ['employee','total_amount','presents','paid_leaves','unpaid_leaves','absents','paiddays','total_working_days','reimbursement_amount','deduction_amount'];
		$form->layout->add('View',null,'salary_details_fields')->set(implode(", ",$row_fields));

		// employee fields
		$emp_fields = array_keys($this->add('xepan\hr\Model_Employee',['addOtherInfo'=>true])->tryLoadAny()->data);
		$form->layout->add('View',null,'emp_fields')->set(implode(", ",$emp_fields));

		$to_remove = ['id','name'];
		$emp_fields = array_diff($emp_fields, $to_remove);

		// salary fields
		$salaries = [];
		foreach ($this->add('xepan\hr\Model_Salary') as $s) {
			$salaries[] =  $s['name'];
		};
		$form->layout->add('View',null,'salary_details_fields')->set(implode(", ",$salaries));


		$form->addSubmit('Generate File');



		// finally create one model with all expressions and all for set 
		$salary_model = $page->add('xepan\hr\Model_EmployeeRow');
		foreach ($sheet_fields as $s_fields) {
			$salary_model->addExpression($s_fields)->set($salary_model->refSQL('salary_abstract_id')->fieldQuery($s_fields));
		}
		foreach ($emp_fields as $emp_fields) {
			$salary_model->addExpression($emp_fields)->set($page->add('xepan\hr\Model_Employee',['addOtherInfo'=>true])->addCondition('id',$salary_model->getField('employee_id'))->fieldQuery($emp_fields));
		}

		foreach ($salaries as $s) {
			$salary_model->addExpression($s)->set($salary_model->refSQL('SalaryDetail')->addCondition('salary',$s)->fieldQuery('amount'));
		}

		$salary_model->addCondition('salary_abstract_id',$this->id);

		if($this->app->stickyGET('format')){
			
			$decimal = $this->app->stickyGET('decimal_digits');

			$format_field->set($_GET['format']);

			$nl="<br/>";
			if($this->app->stickyGET('download')) $nl="\r\n";
			
			$template = $page->add('GiTemplate');
			$template->loadTemplateFromString("{rows}{row}".$_GET['format'].$nl."{/}{/}");
			$report = $page->add('CompleteLister',null,null,$template);

			$report->setModel($salary_model);
			$report->addHook('formatRow',function($g)use($salaries,$decimal){
				foreach ($salaries as $s) {
					$g->current_row[$s] = round($g->model[$s],$decimal);
					$g->current_row[$this->app->normalizeName($s)] = round($g->model[$this->app->normalizeName($s)],$decimal);
				}
			});
			if($this->app->stickyGET('download')){
				$output = $report->getHTML();
				$extension = explode(".", $_GET['filename']);
		    	header("Content-type: text/".(isset($extension[1])?$extension[1]:'plain'));
		        header("Content-disposition: attachment; filename=\"".$_GET['filename']."\"");
		        header("Content-Length: " . strlen($output));
		        header("Content-Transfer-Encoding: binary");
		        echo $output;
		        exit;
			}
			// $page->add('View')->set($report->render());
			// $page->add('View')->setHTML(print_r($salary_model->getRows(),true));
		}else{
			$grid = $page->add('Grid');
			$grid->setModel($salary_model);
		}

		if ($form->isSubmitted()){
			if($form['download'] && !$form['filename']) $form->displayError('filename','PLease specify a filename');

			if($form['download'])
				$page->js()->univ()->newWindow($this->app->url('.',['format'=>$form['format'],'filename'=>$form['filename'],'download'=>$form['download'],'filter'=>1]))->execute();
			else
				$page->js()->reload(['format'=>$form['format'],'filename'=>$form['filename'],'download'=>$form['download']?:0,'decimal_digits'=>$form['decimal_digits'],'filter'=>1])->execute();
		}
	
	}
	
}