<?php


namespace xepan\hr;


class View_ActionBtn extends \CompleteLister{
	public $actions=[];
	public $status= 'StatusHERE';
	public $id;
	public $action_btn_group=null;
	public $status_color = [];

	function init(){
		parent::init();
		$temp_array=[];
		foreach ($this->actions as $value) {
			$temp_array[] = ['action'=>$value,'action_title'=>ucwords(str_replace("_", " ", $value)),'row_id'=>$this->id ,'class'=>($value === 'edit'?'pb_edit':($value === 'delete'?'do-delete':''))];
		}

		$this->setSource($temp_array);

		$this->template->setHtml('status',$this->status);
		$this->template->set('status_label',$this->setLabelColor($this->status));
		if($this->action_btn_group) 
			$this->template->set('action_btn_group',$this->action_btn_group);

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
					'Qualified' =>'success',
					'Converted' =>'success',
					'NeedsAnalysis' => 'danger',
					'Rejected' => 'danger',
					'Draft'=>'default',
					'Submitted' => 'warning',
					'Approved' =>'success',
					'Rejected' => 'danger',
					'Pending' => 'warning',
					'Rejected'=>'danger',
					'Lost'=>'danger',
					'Received'=>'info',
					'Quoted'=>'info',
					'Forwarded'=>'success',
					'Won'=>'success',
					'Processing'=>'warning',
					'Completed'=>'success',
					'Canceled'=>'info',
					'Redesign'=>'info',
					'InProgress'=>'warning',
					'Due'=>'danger',
					'Paid'=>'success',
					'ToReceived'=>'default',
					'Received'=>'info',
					'Dispatch'=>'warning',
					'ReceivedByParty'=>'success',
					'Published'=>'success',
					'UnPublished'=>'danger',
					'OnlineUnpaid'=>'warning',
					'Running'=>'info',
					'Inprogress'=>'warning',
					'Pending'=>'warning',
					'Assigned'=>'success',
					'On-Hold'=>'danger',
					'Onhold'=>'info',
					'Negotiated'=>'info',
					'Closed'=>'success'

				];
		
		$status_color = array_merge($status_color,$this->status_color);

		return $status_color[$status];
	}

	function defaultTemplate(){
		return ['view/action-btn'];
	}
}
