<?php

namespace xepan\hr;

/**
* 
*/
class Model_Employee_Attandance extends \xepan\base\Model_Table{
	public $table = "employee_attandance";
	public $acl = false;
	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','employee_id');
		$this->addField('from_date')->type('datetime');
		$this->addField('to_date')->type('datetime')->defaultValue(null);

		$this->addExpression('fdate')->set('DATE(from_date)');
		$this->addExpression('tdate')->set('DATE(to_date)');

		$this->addExpression('official_day_start')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('in_time')
				]);
		});

		$this->addExpression('official_day_end')->set(function($m,$q){
			return $q->expr('CONCAT([0]," ",[1])',[
					$m->getElement('fdate'),
					$m->refSQL('employee_id')->fieldQuery('out_time')
				]);
		});

		$this->addExpression('actual_day_ending')->set(function($m,$q){
			return $q->expr('IFNULL([0],[1])',[
										$q->getField('to_date'),
										$m->getElement('official_day_end')
									]
							);
		});

		$this->addExpression('late_coming')->set(function($m,$q){
			return $q->expr('TIMESTAMPDIFF(MINUTE,[0],[1])',[
					$m->getElement('official_day_start'),
					$q->getField('from_date'),
				]);
		});

		$this->addExpression('extra_work')->set(function($m,$q){
			return $q->expr('TIMESTAMPDIFF(MINUTE,[0],[1])',[
					$m->getElement('actual_day_ending'),
					$m->getElement('official_day_end'),
				]);
		});

		$this->addExpression('working_hours')->set(function($m,$q){
			return $q->expr('TIMESTAMPDIFF(HOUR,[0],[1])',[
					$q->getField('from_date'),
					$m->getElement('actual_day_ending'),
				]);
		});
	}
}