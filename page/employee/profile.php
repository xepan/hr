<?php
namespace xepan\hr;
class page_employee_profile extends \xepan\base\Page{
	public $title="Employee Profile";
	function init(){
		parent::init();

		$page_url = $this->api->url();
		$this->vp = $this->add('VirtualPage');
		$this->vp->set(function($p)use($page_url){
			$f=$p->add('Form');
			$f->setModel('xepan\base\Contact',['image_id'])->load($this->app->employee->id);
			$f->addSubmit('Save');
			if($f->submitted()){
				$f->save();
				$f->js()->univ()->location($page_url)->execute();
			}
		});

		$this->js('click')->_selector('.profile-img')->univ()->frameURL('Change Image',$this->vp->getURL());

		$employee= $this->add('xepan\hr\Model_Employee')->tryLoadBy('id',$this->app->employee['id']);
		$user=$employee->ref('user_id');
		

		$contact_view = $this->add('xepan\base\View_Contact',null,'profile_view');
		$contact_view->setModel($employee);

		/*Profile View*/
		// $pf=$this->add('Form',null,'profile_view');
		// $pf->setLayout('form/employee/profile');
		// $pf->setModel($employee,['first_name','last_name']);
		// $pf->addSubmit('Update');
		// if($pf->isSubmitted()){
		// 	$pf->save();
		// 	$pf->js()->reload()->execute();
		// }

		/*Basic Informations*/
		$b_f=$this->add('Form',null,'basic_view');
		$b_f->setLayout(['form/employee/basic-info']);
		$b_f->setModel($employee,['epan_id','address','city','state','is_active','country','pin_code']);
		$b_f->addSubmit('Update')->addClass('btn btn-success');
		
		if($b_f->isSubmitted()){
			$b_f->save();
			$b_f->js()->reload()->execute();
		}

		/*Change Password & Reset password*/
		$f=$this->add('Form',null,'password_view');
		$f->setLayout(['form/employee/password']);
		$f->addField('password','old_password');
		$f->addField('password','new_password');
		$f->addField('password','retype_password');
		$f->addSubmit('Change')->addClass('btn btn-success');

		if($f->isSubmitted()){


			if(!$this->app->auth->verifyCredentials($user['username'],$f['old_password'])){
				$f->displayError($f->getElement('old_password'),'Old Password not Match');
			}	
			if($f['new_password']==''){
				$f->displayError($f->getElement('new_password'),'New Password Required Field');
			}
			if($f['new_password']!= $f['retype_password']){
				$f->displayError($f->getElement('retype_password'),'New Password Not Match');
			}
			$this->app->auth->model['password']=$f['new_password'];
			$this->app->auth->model->save();
			$f->js(null,$f->js()->univ()->successMessage('Password Change'))->reload()->execute();
		}
	}
	function defaultTemplate(){
		return ['page/employee/profile'];
	}
}