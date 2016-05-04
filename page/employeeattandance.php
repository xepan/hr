<?php

namespace xepan\hr;

class page_employeeattandance extends \Page{
	public $title = "Employee Attandance";
	function init(){
		parent::init();

		$movement = $this->add('xepan\hr\Model_Employee_Movement');
		$grid = $this->add('xepan\hr\Grid',null,null,['view\employee\attandance-grid']);
		$grid->setModel($movement,['employee','direction','time','reason','narration']);

		$grid->add('xepan\base\Controller_Avatar',['options'=>['size'=>40,'border'=>['width'=>0]],'name_field'=>'employee','default_value'=>'']);
		$frm=$grid->addQuickSearch(['employee']);
		$frm->addField('DatePicker','from_date','From');
		$frm->addField('DatePicker','to_date','To');
		$frm_drop = $frm->addField('DropDown','direction')->setEmptyText('Both')->setValueList(['In'=>'In','Out'=>'Out']);
		$frm_drop->js('change',$frm->js()->submit());

		$frm->addHook('applyFilter',function($frm,$m){
			if($frm['direction'])
				$m->addCondition('direction',$frm['direction']);
			if($frm['from_date'])
				$m->addCondition('time','>',$frm['from_date']);
			if($frm['to_date'])
				$m->addCondition('time','<',$frm['to_date']);
		});
	}
}



