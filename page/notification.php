<?php

namespace xepan\hr;

class page_notification extends \xepan\base\Page{
	public $title="Activity Notification";
	function init(){
		parent::init();

		$start_date = $this->app->stickyGET('from_date');
		$end_date = $this->app->stickyGET('to_date');
		$document = $this->app->stickyGET('document');

		$activity = $this->add('xepan\hr\Model_Activity');
		
		$activity->addExpression('document_type')->set(function($m,$q){
			$document = $this->add('xepan\hr\Model_Document');
			$document->addCondition('id',$m->getElement('related_document_id'));
			$document->setLimit(1);
			
			return $document->fieldQuery('type');
		});	

		$activity->addCondition('notify_to','like','%"'.$this->app->employee->id.'"%');		

		if($start_date){
			$activity->addCondition('created_at','>',$start_date);
			$activity->addCondition('created_at','<',$end_date);				
		}
		
		if($document){			
			$activity->addCondition('document_type',explode(',', $document));
		}

		$activity_doc = $this->add('xepan\hr\Model_Activity');
		$activity_doc->addExpression('document_type')->set(function($m,$q){
			$document = $this->add('xepan\hr\Model_Document');
			$document->addCondition('id',$m->getElement('related_document_id'));
			$document->setLimit(1);
			
			return $document->fieldQuery('type');
		});	

		$activity_doc->_dsql()->group('document_type');
		$activity_doc->title_field = 'document_type';

		// document array
		$document_type_array = [];
		
		foreach ($activity_doc as $activity_m) {
			$document_type_array[$activity_m->id] = $activity_m['document_type']?:"Other";
		}	
		$form = $this->add('Form');
		$date_range_field = $form->addField('DateRangePicker','date_range')
								 ->setStartDate($this->app->now)
								 ->setEndDate($this->app->now)
								 ->getBackDatesSet();
		$document_field = $form->addField('xepan\base\DropDown','document_type');						 
		$document_field->setAttr(['multiple'=>'multiple']);
		$document_field->setModel($activity_doc);

		$form->addSubmit('Filter')->addClass('xepan-push btn btn-primary');
		
		$g = $this->add('xepan\hr\Grid',null,null,['view/activity/activity-grid']);
		
		if($form->isSubmitted()){


			$selected_doc_type_array  = [];

			if($form['document_type']){
				foreach (explode(",", $form['document_type']) as $key => $value) {
					$selected_doc_type_array[$value] = $value;
				}

				$selected_doc_type_array = array_intersect_key($document_type_array,$selected_doc_type_array);
			}

			$form->js(null,$g->js()
								->reload(
									[
										'from_date'=>$date_range_field->getStartDate(),
										'to_date'=>$date_range_field->getEndDate(),
										'document'=>implode(',',$selected_doc_type_array)
									]))->univ()->successMessage('wait ... ')->execute();
		}

		$g->setModel($activity);
		$g->addPaginator(50);

		$this->add('xepan\hr\Model_Employee')
						->load($this->app->employee->id)
						->set('notified_till',$this->add('xepan\hr\Model_Activity')->setOrder('id','desc')->tryLoadAny()->get('id'))
						->save();
				$this->app->employee->reload();
				$this->app->memorize($this->app->epan->id.'_employee', $this->app->employee);
	}
}