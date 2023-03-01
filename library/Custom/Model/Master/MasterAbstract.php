<?php
namespace Library\Custom\Model\Master;

class MasterAbstract {
	
	static protected $_instance;
	
	protected $_tableClass;
	protected $_valueCol = 'id';
	protected $_labelCol = 'name';
	
	/**
	 * @var Custom_Db_Table_Row
	 */
	protected $_rowset;
	
	protected $_options = array();
	
	protected $_where = array();
	
	/**
	 * @var App\Repositories\BaseRepository
	 */
	protected $_table;
	
	protected function __construct($options = array()) {
		foreach ($options as $key => $value) {
			$method = camelize('set_' . $key);
			if (method_exists($this, $method)) {
				$this->{$method}($value);
			}
		}

		$this->reload();
	}
	
	protected function _setOptions($options) {
		
	}
	
	protected function _where() {
		return array();
	}
	
	public function getOptions() {
		return $this->_options;
	}
	
	public function get($id) {
		return isset($this->_options[$id]) ? $this->_options[$id] : null;
	}
	
	public function reload() {
		if ($this->_tableClass == 'HpPage') {
			$this->_rowset = $this->_where->get();
		} else {
			$this->_rowset = $this->_table->fetchAll($this->_where);
		}
		// $this->_rowset = $this->_table->whereRaw($this->_where)->get();
		$this->_createOptions($this->_rowset);
	}
	
	protected function _createOptions($rowset) {
		$this->_options = array();
		foreach ($rowset as $row) {
			$this->_options[$row->{$this->_valueCol}] = $row->{$this->_labelCol};
		}
	}
	
	public function addWhere($where) {
		// $this->_where .= array_merge($this->_where, $where);
		$this->_where .= $where;
		return $this;
	}
	
	public function setWhere($where) {
		$this->_where = $where;
		return $this;
	}
	
	/**
	 * @return Custom_Db_Table_Row
	 */
	public function getRowset() {
		return $this->_rowset;
	}
	
	static public function init($options = array()) {
		return static::getInstance($options);
	}
	
	static public function getInstance($options = array()) {
		if (!static::$_instance) {
			static::$_instance = new static($options);
		}
		
		return static::$_instance;
	}
}