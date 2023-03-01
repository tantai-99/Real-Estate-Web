<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Scopes\DeleteScopes;

 
class Model extends BaseModel {
    protected $repository;
	protected $sorfDelete = true;
    /**
	 * @array
	 */
	protected $_cryptMap = array();

    public function getRepository() {
        return $this->repository;
    }

    public function __construct() {
		parent::boot();

		static::addGlobalScope(new DeleteScopes);
		$this->init();
	}

	public function init(){}

    public function __get($columnName) {
		$value = parent::__get($columnName);
		if ($value && isset($this->_cryptMap[$columnName])) {
			$value = $this->_cryptMap[$columnName]->decrypt($value);
		}
		return $value;
	}
	
	public function __set($columnName, $value) {
		if (!isEmpty($value) && isset($this->_cryptMap[$columnName])) {
			$value = $this->_cryptMap[$columnName]->encrypt($value);
		}
		return parent::__set($columnName, $value);
	}

	public function save(array $options = []) {
		$this->update_date = date('Y-m-d H:i:s');
		parent::save();
	}

	public function detete($forceDelete = false) {
		if($forceDelete) {
			parent::forceDelete();
		} else {
			parent::delete();
		}
		return;
	}
	
	public function getIterator() {
		return new ArrayIterator($this->toArray());
	}
	
	public function toArray() {
		$data = parent::toArray();
		foreach ($data as $columnName => $value) {
			if (isset($this->_cryptMap[$columnName])) {
				$data[$columnName] = $this->__get($columnName);
			}
		}
		return $data;
	}

	public function setFromArray($data) {
		foreach($data as $columnName=>$value) {
			$this->__set($columnName, $value);
		}
		return $this;
	}
	
	public function copyPolling($str = "Polling:") {
		echo str_pad($str, ini_get('output_buffering'), ' ', STR_PAD_RIGHT) . date('Y-m-d H:i:s') ."<br/>\n";
		flush();
		ob_flush();
	}
	// public function newCollection(array $models = Array()) {
	// 	return new Extensions\CustomCollection($models);
	// }
}