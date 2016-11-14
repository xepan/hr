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

		$c = $this->add('xepan\hr\CRUD',null,null,['view\graphicalreportbuilder']);
		$c->setModel($m,['name']);
		if(!$c->isEditing()){
			$c->grid->addColumn('link','run')->setTemplate('<a href="'.$this->app->url('xepan/base/graphicalreport/runner',[])->getURL().'&report_id={$id}">{$name}</a>');
		}
	}
}