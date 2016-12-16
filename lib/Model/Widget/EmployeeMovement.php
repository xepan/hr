<?php

namespace xepan\hr;

class Model_Widget_EmployeeMovement extends \xepan\hr\Model_Employee{

	function init(){
		parent::init();

		$this->addCondition('status','Active');

		$date = $this->app->today;
		$this->addExpression('first_in')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','In')
					->setOrder('movement_at','asc')
					->setLimit(1)
					->fieldQuery('movement_at');
		});

		$this->addExpression('last_out')->set(function($m,$q)use($date){
			return $m->refSQL('EmployeeMovements')
					->addCondition('date',$date)
					->addCondition('direction','Out')
					->setOrder('movement_at','desc')
					->setLimit(1)
					->fieldQuery('movement_at');
		});


		$this->addExpression('is_late')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]>= CONCAT('[1]',' ',[2]),1,0)",
					  [
						$m->getElement('first_in'),
						$date,
						$m->getElement('in_time')
					  ]
					);
		});

		$this->addExpression('last_direction')->set(function($m,$q){
			$temp = $m->refSQL('EmployeeMovements')
			  			->setOrder('movement_at','desc')
			  			->setLimit(1);

			return $q->expr('IFNULL([0],"Out")',[$temp->fieldQuery('direction')]);
		});

		$this->addExpression('last_direction_today')->set(function($m,$q){
			$temp = $m->refSQL('EmployeeMovements')
						->addCondition('date',$this->app->today)
			  			->setOrder('movement_at','desc')
			  			->setLimit(1);

			return $q->expr('IFNULL([0],"Out")',[$temp->fieldQuery('direction')]);
		});

		
		$this->addExpression('is_in')->set(function($m,$q){
			return $q->expr(
					"IF([0]='In' AND [1]=	[2],'In','Out')",
					  [
						$m->getElement('last_direction'),
						$m->getElement('date'),
					  	$this->app->today
					  ]
					);
		});

		$this->addExpression('is_out')->set(function($m,$q){
			return $q->expr(
					"IF([0]='Out','Out','In')",
					  [
						$m->getElement('last_direction_today'),
					  ]
					);
		});
		
		$this->addExpression('in_color')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]<= CONCAT('[1]',' ',[2]),'success','danger')",
					  [
						$m->getElement('first_in'),
						$date,
						$m->getElement('in_time')
					  ]
					);
		});

		$this->addExpression('out_color')->set(function($m,$q)use($date){
			return $q->expr(
					"IF([0]>= CONCAT('[1]',' ',[2]),'success','danger')",
					  [
						$m->getElement('last_out'),
						$date,
						$m->getElement('out_time')
					  ]
					);
		});
	}
}