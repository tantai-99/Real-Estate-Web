<?php

namespace App\Rules;

class HpPageId extends CustomRule
{
    protected $_table;
	protected $_hpId = 0;
	protected $_where = array();
	
	protected $_idCol = 'id';
	
	protected $_row;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($options = array())
    {
        if (!is_array($options) || func_num_args() > 1) {
			$args = func_get_args();
			$options = array();
			if (isset($args[0])) $options['table'] = $args[0];
			if (isset($args[1])) $options['hp_id'] = $args[1];
			if (isset($args[2])) $options['where'] = $args[2];
		}
		
		if (isset($options['table'])) {
			$this->setTable($options['table']);
		}
		if (isset($options['hp_id'])) {
			$this->setHpId($options['hp_id']);
		}
		if (isset($options['where'])) {
			$this->setWhere($options['where']);
		}
    }

    public function setTable($table) {
		$this->_table = $table;
		return $this;
	}
	
	public function getTable() {
		return $this->_table;
	}
	
	public function setHpId($hpId) {
		$this->_hpId = $hpId;
		return $this;
	}
	
	public function getHpId() {
		return $this->_hpId;
	}
	
	public function setWhere($where) {
		$this->_where = $where;
		return $this;
	}
	
	public function getRow() {
		return $this->_row;
	}

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $value = (int) $value;	
		if ($value === 0) {
			return true;
		}

		$where = array_merge(array([$this->_idCol, $value], ['hp_id', $this->_hpId]), $this->_where);
		$this->_row = $this->_table->fetchRow($where);
		if (!$this->_row) {
			$fail('ページIDが無効です。');
			return false;
		}
		return true;
    }
}
