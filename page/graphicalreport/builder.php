<?php

namespace xepan\hr;


class page_graphicalreport_builder extends \xepan\base\Page {

	public $title ="Graphical Report Builder";

	public $widget_list = [];
	public $entity_list = [];

	function init(){
		parent::init();

		$this->app->hook('widget_collection',[&$this->widget_list]);
		$this->app->hook('entity_collection',[&$this->entity_list]);
	}

	function page_index(){
		$m = $this->add('xepan\base\Model_GraphicalReport');

		if(!$this->app->auth->model->isSuperUser())			
			$m->addCondition('permitted_post','like','%"'.$this->app->employee['post_id'].'"%');
		
		$c = $this->add('xepan\hr\CRUD',null,null,['view\graphicalreportbuilder']);
		$c->setModel($m,['name']);
		if(!$c->isEditing()){
			$import_btn=$c->grid->addButton('import')->addClass('btn btn-primary');

			$p=$this->add('VirtualPage');
			$p->set(function($p){
				$f=$p->add('Form');
				$f->addField('text','json');
				$f->addSubmit('Go');
				
				if($f->isSubmitted()){
					$import_m=$this->add('xepan\base\Model_GraphicalReport');

					$import_m->importJson($f['json']);	
					
					$f->js()->reload()->univ()->successMessage('Done')->execute();
				}
			});
			if($import_btn->isClicked()){
				$this->js()->univ()->frameURL('Import',$p->getUrl())->execute();
			}

			$c->grid->addColumn('link','run')->setTemplate('<a href="'.$this->app->url('xepan/hr/graphicalreport/runner',[])->getURL().'&report_id={$id}">{$name}</a>');
			
			$p=$this->add('VirtualPage');
			$p->set(function($p){
					$export_m=$this->add('xepan\base\Model_GraphicalReport')->load($p->id);
					$json=$export_m->exportJson();
					$p->add('View')->set($json);
			});

			$p->addColumn("export", "export", "export", $c->grid);
		}
	}
}