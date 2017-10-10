<?php


namespace xepan\hr;

/**
	
	Model on owner grid must have following expresion defined 

*/

class Controller_Grid_Format_Employee extends \Controller_Grid_Format {
	/**
     * Initialize field
     *
     * Note: $this->owner is Grid object
     * 
     * @param string $name Field name
     * @param string $descr Field title
     *
     * @return void
     */
    public function initField($name, $descr) {
        if(!isset($descr['descr'])) $this->owner->columns[$name]['descr'] = ucwords(str_replace('_', ' ', $name));
        if($this->owner->hasColumn($descr['actor_field'])) $this->owner->removeColumn($descr['actor_field']);
        if($this->owner->hasColumn($descr['actor_field'].'_id')) $this->owner->removeColumn($descr['actor_field'].'_id');
    	// $this->owner->model->addExpression('data1')->set('"123"');
    }
    
    /**
     * Format output of cell in particular row
     *
     * Note: $this->owner is Grid object
     * 
     * @param string $field Field name
     * @param array $column Array [type=>string, descr=>string]
     *
     * @return void
     */
    public function formatField($field, $column) {
    	$this->owner->current_row[$field]=$this->owner->model[$column['actor_field']];
    }
}