<?php

namespace xepan\hr;

class page_employee_leave extends \xepan\hr\page_employee_myhr{
	public $title="My Leave";
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$new_leave_tab = $tabs->addTab('Apply Leave');
		$avail_leave=0;

		$emp_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$emp_leave_m->addCondition('employee_id',$this->app->employee->id);
		
		$leave_emp_m = $this->add('xepan\hr\Model_Employee_LeaveAllow');
		$leave_emp_m->addCondition('employee_id',$this->app->employee->id);
		$f = $new_leave_tab->add('Form');
		$f->add('xepan\base\Controller_FLC')
			->addContentSpot()
			->makePanelsCoppalsible()
			->layout([
				'allow_leave~Leave Type'=>'Apply For Leave~c1~4',
				'from_date'=>'c3~2',
				'to_date'=>'c4~2',
				'narration'=>'c6~4',
				'FormButtons~'=>'c5~2'
			]);

		$allow_leave_f = $f->addField('Dropdown','allow_leave');
		$allow_leave_f->setEmptytext('Please Select');
		$allow_leave_f->setModel($leave_emp_m);
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addField('text','narration');

		$f->addSubmit('Apply')->addClass('btn btn-primary');

		$draft_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$draft_leave_m->addCondition('created_by_id',$this->app->employee->id);
		$draft_leave_m->addCondition('status',"Draft");
		$draft_grid = $new_leave_tab->add('xepan\hr\CRUD',['allow_add'=>false]);
		$draft_leave_m->getElement('emp_leave_allow')->caption('Leave Type');

		$draft_grid->setModel($draft_leave_m,['emp_leave_allow','from_date','to_date','no_of_leave','narration','status']);
		$draft_grid->grid->addSno();
		$draft_grid->removeAttachment();
		$draft_grid->grid->removeColumn('status');
		// $draft_grid->addQuickSearch(['employee']);
		
		if($f->isSubmitted()){
			$allow_leave_m = $this->add('xepan\hr\Model_Employee_LeaveAllow');
			$allow_leave_m->load($f['allow_leave']);
			$date = $this->app->my_date_diff($f['from_date'],$f['to_date']);
			
			if(!$allow_leave_m['allow_over_quota']){
				if($date['days'] > $allow_leave_m['no_of_leave']){
					$f->displayError('to_date','Not allow more than employee Leave');
				}
			}

			$emp_leave_m['emp_leave_allow_id']=$allow_leave_m->id;		
			$emp_leave_m['from_date']=$f['from_date'];		
			$emp_leave_m['to_date']=$f['to_date'];
			$emp_leave_m['narration']=$f['narration'];
			$emp_leave_m->save();		

			$js =[
					$f->js()->univ()->successMessage('Done'),
					$draft_grid->js()->reload()
				];	
			$f->js(null,$js)->reload()->execute();
		}

		// leave quota
		$leave_tab = $tabs->addTab('My Leave Quota');
		$allow_leave_model = $leave_tab->add('xepan\hr\Model_Employee_LeaveAllow');
		$allow_leave_model->addCondition('employee_id',$this->app->employee->id);
		
		$allow_leave_model->addExpression('leave_taken')->set(function($m,$q){
			$emp_leave = $m->add('xepan\hr\Model_Employee_Leave')
							->addCondition('emp_leave_allow_id',$q->getField('id'))
							->addCondition('employee_id',$q->getField('employee_id'))
							->addCondition('from_date','>=',$q->getField('effective_date'))
							->addCondition('status','Approved')
							;
			return $q->expr('([0])',[$emp_leave->sum('no_of_leave')]);
		});

		$allow_leave_model->addExpression('available_leave')->set(function($m,$q){
			return '"0"';
			// return $q->expr('([0]-[1])',[$m->getElement('no_of_leave'),$m->getElement('leave_taken')]);
		});

		$allow_leave_grid = $leave_tab->add('xepan\hr\Grid');
		$allow_leave_grid->setModel($allow_leave_model);
		$allow_leave_grid->addHook('formatRow',function($g){
			$emp = $this->add('xepan\hr\Model_Employee')->load($g->model['employee_id']);
			$g->current_row_html['available_leave'] = $emp->getAvailableLeave($this->app->now,$g->model['leave_id']);
		});

		// history
		$his_tab = $tabs->addTab('Leave History/Status');
		$his_leave_m = $this->add('xepan\hr\Model_Employee_Leave');
		$his_leave_m->addCondition('created_by_id',$this->app->employee->id);
		$his_leave_m->setOrder('id','desc');

		$his_grid = $his_tab->add('xepan\hr\Grid');
		$his_grid->setModel($his_leave_m,['emp_leave_allow','from_date','to_date','no_of_leave','narration','status']);
		$his_grid->addQuickSearch(['employee']);
		$his_grid->addPaginator($ipp=25);
	}
}