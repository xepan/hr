<?php
namespace xepan\hr;
class page_employee_setting extends \xepan\base\Page{
	public $title="Settings";
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
        $permitted_dashboards = $tabs->addTab('Permitted Dashboards');
		
		$default_permitted_list =[];
		switch($this->app->employee->ref('post_id')->get('permission_level')){
			case 'Global':
				$default_permitted_list=['Global','Sibling','Department','Individual'];
				break;
			case 'Sibling':
				$default_permitted_list=['Sibling','Department','Individual'];
				break;
			case 'Department':
				$default_permitted_list=['Department','Individual'];
				break;
			default:				
				$default_permitted_list=['Individual'];
		}

		$permitted_reports_model = $this->add('xepan\base\Model_GraphicalReport');
		$permitted_reports_model->addCondition([
				['name','in',$default_permitted_list],
				['permitted_post','like','%"'.$this->app->employee['post_id'].'"%'],
				['created_by_id',$this->app->employee->id],
			]);

		$form = $permitted_dashboards->add('Form');        
		$form->addField('DropDown','permitted_dashboards')->setModel($permitted_reports_model);
		$form->addSubmit('Save')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$employee_m = $this->add('xepan\hr\Model_Employee');
			$employee_m->load($this->app->employee->id);

			$employee_m['graphical_report_id'] = $form[''];
		}

		$deactivate_account = $tabs->addTab('Deactivate Account');

		$task = $deactivate_account->add('xepan\projects\Model_Task');
		$task->addCondition('status','<>',"Completed")
		    	 ->addCondition($task->dsql()->orExpr()
		    					     ->where('assign_to_id',$this->app->employee->id)
		    					     ->where($task->dsql()->andExpr()
	    									      ->where('created_by_id',$this->app->employee->id)
	    									      ->where('assign_to_id',null)));
		$total_uncomplete_task = $task->count()->getOne();    	 
		$deactivate_account->add('H1')->setHtml("<a href='xepan_projects_mytasks' target='_blank'> UnComplete Task: ".$total_uncomplete_task."</a>");

		if($total_uncomplete_task){
			$btn = $deactivate_account->add('Button')->set('Deactivate My Account')->addClass('btn btn-danger');

			if($btn->isClicked()){
				$deactivate_emp = $this->add('xepan\hr\Model_Employee');
				$deactivate_emp->load($this->app->employee->id);
				$deactivate_emp['status'] = "DeactivateRequest";
				$deactivate_emp->save();
				
				$this->js(null,$this->js()->univ()->successMessage('Request Send'))->reload()->execute();

			}
		}
	}
}