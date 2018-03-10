<?php

namespace xepan\hr;

class Model_SalaryTemplate extends \xepan\base\Model_Table{
	public $table = "salary_template";
	public $actions = ['All'=>['view','update_from_salary','edit','delete']];
 	public $acl_type = "SalaryTemplate";

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id)->system(true);
		$this->addField('name');
		
		$this->hasMany('xepan\hr\SalaryTemplateDetails','salary_template_id');
		
		$this->addHook('beforeDelete',$this);
	}

	function beforeDelete(){

		$post = $this->add('xepan\hr\Model_Post');
		$post->addCondition('salary_template_id',$this->id);
		if($post->count()->getOne()){
			throw new \Exception("first remove salary template from post");
		}

		$model = $this->add('xepan\hr\Model_SalaryTemplateDetails');
		$model->addCondition('salary_template_id',$this->id)
				->deleteAll();
		
	}

	function page_update_from_salary($page){
		$page->add('View')->set('Template: '.$this['name'])->addClass('alert alert-success');

		$form = $page->add('Form');		
		$form->addField('checkBox','update_all_amount_of_added_salary')->set(1);
		$multiselect_field = $form->addField('dropDown','salary','Select Salary for amount update');
		$multiselect_field->addClass('multiselect-full-width')
					->setAttr(['multiple'=>'multiple']);
		$multiselect_field->setModel('xepan\hr\Model_Salary');

		$form->addSubmit('Update');

		if($form->isSubmitted()){

			if(!$form['update_all_amount_of_added_salary'] AND $form['salary']){
				$salaries = explode(",", $form['salary']);
				foreach ($salaries as $s_id) {
					$sd_model = $this->add('xepan\hr\Model_SalaryTemplateDetails');
					$sd_model->addCondition('salary_template_id',$this->id);
					$sd_model->addCondition('salary_id',$s_id);
					$sd_model->tryLoadAny();
					$sd_model['amount'] = "";
					$sd_model->save();
				}
			}else{
				$sd_model = $this->add('xepan\hr\Model_SalaryTemplateDetails');
				$sd_model->addCondition('salary_template_id',$this->id);
				foreach ($sd_model as $sal) {
					$sal['amount'] = "";
					$sal->save();
				}
			}
			

			$this->app->page_action_result = $form->js(null,$form->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Salary of Template Update');

		}

	}
}