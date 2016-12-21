<?php

namespace xepan\hr;


class page_graphicalreport_runner extends \xepan\base\Page {

	public $title ="Graphical Report";

	public $widget_list = [];
	public $entity_list = [];

	public $filter_form;
	
	function init(){
		parent::init();

		$runner_view = $this->add('xepan\hr\View_GraphicalReport_Runner');
	}
}