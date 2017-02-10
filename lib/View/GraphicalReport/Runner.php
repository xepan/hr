<?php

namespace xepan\hr;

class View_GraphicalReport_Runner extends \View{
	public $widget_list;
	public $entity_list;
	public $filter_form;
	public $report_id;
	public $report_type='chart';

	function init(){
		parent::init();

		
		$this->app->hook('widget_collection',[&$this->widget_list]);
		$this->app->hook('entity_collection',[&$this->entity_list]);

		$report_id = $this->api->stickyGET('report_id');

		foreach ($_GET as $get=>$value) {				
			if($value AND !in_array($get, ['page','cut_object','cut_page'])){
                $this->api->stickyGET($get);
                $this->$get = $value;
            }
		}
		
		$this->filter_form = $this->add('Form',null,'filter_form');
		
		if($report_id == null)
			$report_id = $this->report_id;
				
		$rpt = $this->add('xepan\base\Model_GraphicalReport')->tryLoadBy('id',$report_id);

		if(!$rpt->loaded()){
			$this->add('View')->set('Sorry! No dashboard found');
			return;
		}
		
		$this->title = $rpt['name'];
		$report_w = $rpt->ref('xepan\base\GraphicalReport_Widget')->addCondition('is_active',true)->setOrder('order','asc');
		
		foreach ($report_w as $widget) {
			$w = $this->add('xepan\base\Widget_Wrapper');
			$w->addClass('widget');
			if($this->report_type==='chart')
				$w->addClass('col-md-'.$widget['col_width']);
			$widget = $w->add($widget['class_path'],['report'=>$this]);
			$widget->setFilterForm($this->filter_form);
		}

		$this->filter_form->addSubmit('Filter');

		if($this->filter_form->isSubmitted()){
			$form_result = $this->filter_form->get();
			if($this->filter_form->hasElement('date_range')){
				$form_result['start_date'] = $this->filter_form->getElement('date_range')->getStartDate();
				$form_result['end_date'] = $this->filter_form->getElement('date_range')->getEndDate();
			}

			$this->js()->reload($form_result)->execute();
		}

		if(@$this->report_type === 'chart')
			$this->js(true)->_load('masonry.pkgd.min')->masonry(['itemSelector'=>'.widget'])->_selector('.widget-grid');
	}

	function enableFilterEntity($filter_entity){
		if(!in_array($filter_entity ,array_keys($this->entity_list)))
			throw $this->exception('Required entity is not exported by any application')
						->addMoreInfo('required_entity',$filter_entity)
						;

		if($this->filter_form->hasElement($filter_entity)) return;

		$fld = $this->filter_form->addField($this->entity_list[$filter_entity]['type'],$filter_entity,$this->entity_list[$filter_entity]['caption']?:null);
		
		if($this->entity_list[$filter_entity]['model']){
			$model = $fld->setModel($this->entity_list[$filter_entity]['model']);
			
			if($model instanceof \xepan\hr\Model_Employee)
				$model->addCondition('status','Active');

			if($fld->hasMethod('setEmptyText'))
                $fld->setEmptyText('Please select');
		}
		
		if(isset($this->$filter_entity))
			$fld->set($this->$filter_entity);

		if($fld instanceof \Form_Field_DateRangePicker){
			$fld->getFutureDatesSet();
			if(!isset($this->start_date)) $this->start_date = $this->app->today;
			if(!isset($this->end_date)) $this->end_date = $this->app->today;
		}

		return $fld;

	}

	function render(){		
		$this->app->js('chart_rendered','console.log(123)');//->masonry(['itemSelector'=>'.widget'])->_selector('.widget-grid');
		return parent::render();
	}

	function defaultTemplate(){
		return ['widget/runner'];
	}
}