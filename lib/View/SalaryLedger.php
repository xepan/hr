<?php

namespace xepan\hr;

class View_SalaryLedger extends \View{
	public $employee_id;
	public $show_balance=true;
	function init(){
		parent::init();

		if(!$this->employee_id){
			$this->add('View_Info')->set('must pass employee id to view record');
			return;
		}

		$employee_row = $this->add('xepan\hr\Model_EmployeeRow')
						->addCondition('employee_id',$this->employee_id)
						->setOrder('created_at','asc');
		$due_field = $employee_row->getElement('net_amount');
		$due_field->caption('Due Amount');
		
		$paid_field = $employee_row->getElement('total_amount');
		$paid_field->caption('Paid Amount');

		$grid = $this->add('xepan\hr\Grid');
		$grid->addColumn('print_pay_slip');
		$grid->setModel($employee_row,['created_at','net_amount','total_amount']);
		$grid->addColumn('balance');

		$grid->addColumn('view_pay_slip')->setTemplate('<a href="#pc" class="do-view-pay-slip">View Pay Slip</a>','view_pay_slip');

		$this->balance = 0;
		$grid->addHook('formatRow',function($g){
			$paid_amount = 0;
			$due_amount = 0;
			if($g->model['total_amount'])
				$g->current_row['total_amount'] = $paid_amount = $g->model['total_amount'];
			else
				$g->current_row['total_amount'] = 0;

			if($g->model['net_amount'])
				$g->current_row['net_amount'] = $due_amount = $g->model['net_amount'];
			else
				$g->current_row['net_amount'] = 0;

			$this->balance = $this->balance + ($due_amount - $paid_amount);
			$g->current_row['balance'] = $this->balance;

			$g->current_row_html['print_pay_slip'] = '<a class="btn btn-primary" target="_blank" href="'.$this->app->url('xepan_hr_printpayslip',['employee_row'=>$g->model->id,'cut_page'=>1]).'">Print Pay Slip</a>';
			$g->current_row_html['view_pay_slip'] = '<a class="btn btn-primary do-view-pay-slip" target="_blank" data-employe-row-id="'.$g->model->id.'">View Pay Slip</a>';
		});
		$grid->on('click','.do-view-pay-slip')->univ()->frameURL('Pay Slip',[$this->app->url('xepan_hr_printpayslip'),'employee_row'=>$this->js()->_selectorThis()->data('employe-row-id')]);
		if(!$this->show_balance) $grid->removeColumn('balance');
	}
}