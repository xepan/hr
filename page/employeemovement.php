<?php

namespace xepan\hr;

class page_employeemovement extends \xepan\base\Page{
	public $title = "Employee Movement";
	function init(){
		parent::init();

		$movement = $this->add('xepan\hr\Model_Employee_Movement')->setOrder('time','desc');
		$movement->addCondition('time','>',$this->app->today);
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);
		$grid->setModel($movement,['employee','direction','time','reason','narration']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>40,'border'=>['width'=>0]],'name_field'=>'employee','default_value'=>'']);
		$grid->addPaginator(50);
		$frm=$grid->addQuickSearch(['employee']);
		$frm_emp = $frm->addField('dropdown','emp')->setEmptyText('Select An Employee');
		$frm_emp->setModel('xepan\hr\Model_Employee');
		$frm->addField('DatePicker','date');
				
		$frm_emp->js('change',$frm->js()->submit());

		$frm->addHook('applyFilter',function($frm,$m){
			if($frm['emp'])
				$m->addCondition('employee_id',$frm['emp']);
			if($frm['date'])										
				$m->addCondition('time','>',$frm['date']);
		});
	}
}



