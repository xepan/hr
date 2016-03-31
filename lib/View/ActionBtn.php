<?php


namespace xepan\hr;


class View_ActionBtn extends \CompleteLister{
	public $actions=[];
	public $status= 'StatusHERE';
	public $id;

	function init(){
		parent::init();
		$temp_array=[];
		foreach ($this->actions as $value) {
			$temp_array[] = ['action'=>$value,'action_title'=>ucwords(str_replace("_", " ", $value)),'row_id'=>$this->id];
		}

		$this->setSource($temp_array);

		$this->template->set('status',$this->status);
		$this->template->set('status_label',$this->setLabelColor($this->status));
			

		if(empty($temp_array)){
			$this->template->del('dropdown');
			$this->template->set('col_span','12');
		}
	}

	function setLabelColor($status){

		$status_color = 
				[
					'Active' => 'success',
					'InActive' => 'danger',
					'Open'=>'warning',
					'Converted' =>'success',
					'Rejected' => 'danger',
					'Draft'=>'default',
					'Submitted' => 'warning',
					'Approved' =>'success',
					'Rejected' => 'danger',
					'Pending' => 'warning',
					'Rejected'=>'danger',
					'Received'=>'info',
					'Forwarded'=>'success',
					'Processing'=>'warning',
					'Completed'=>'success',
					'Canceled'=>'info',
					'Redesign'=>'info',
					'InProgress'=>'warning',
					'Due'=>'danger',
					'Paid'=>'success'
				];

		return $status_color[$status];
	}

	function defaultTemplate(){
		return ['view/action-btn'];
	}
}