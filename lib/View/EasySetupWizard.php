<?php


namespace xepan\hr;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();
		if($this->add('xepan\hr\Model_Department')->count()->getOne() <= 1000){
			$v = $this->add('xepan\base\View_Wizard_Step');
			$vx= $this->add('View')->set(rand(100,999));
			$v->setTitle('Hi');
			$v->setMessage('Hi');
			$v->setHelpURL('#');
			$v->setAction('I M ATION',$vx->js()->reload());
		}
	}
}