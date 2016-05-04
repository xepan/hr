<?php

namespace xepan\hr;

class page_employeemovement extends \Page{
	public $title = "Employee Movement";
	function init(){
		parent::init();

		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);
		$grid->setModel($movement,['employee','direction','time','reason','narration']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>40,'border'=>['width'=>0]],'name_field'=>'employee','default_value'=>'']);
		$frm=$grid->addQuickSearch(['employee']);
		$frm->addField('DatePicker','from_date','From');
		$frm->addField('DatePicker','to_date','To');
		$frm_drop = $frm->addField('DropDown','direction')->setEmptyText('Select A Direction')->setValueList(['In'=>'In','Out'=>'Out']);
		$frm_emp = $frm->addField('dropdown','emp')->setEmptyText('Select An Employee');
		$frm_emp->setModel('xepan\hr\Model_Employee');
		
		$frm_drop->js('change',$frm->js()->submit());
		$frm_emp->js('change',$frm->js()->submit());

		$frm->addHook('applyFilter',function($frm,$m){
			if($frm['emp'])
				$m->addCondition('employee_id',$frm['emp']);
			if($frm['direction'])
				$m->addCondition('direction',$frm['direction']);
			if($frm['from_date'])
				$m->addCondition('time','>',$frm['from_date']);
			if($frm['to_date'])
				$m->addCondition('time','<',$frm['to_date']);
		});
	}
}



