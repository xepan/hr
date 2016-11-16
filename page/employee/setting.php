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
	}
}