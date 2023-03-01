<?php
namespace Library\Custom\Model\Master;

use App\Repositories\MColor\MColorRepositoryInterface;
use Illuminate\Support\Facades\App;

class Color extends MasterAbstract {
	/**
	 * @var Library\Custom\Model\Master\Color
	 */
	static protected $_instance;
	
	protected $_table ='MColor';
	
	protected $_optionsByTheme = array();
	
	function __construct() {
		
		$this->_table =App::make(MColorRepositoryInterface::class);
		parent::__construct();
	}

	public function reload() {
		parent::reload();
		
		$this->_optionsByTheme = array();
		foreach ($this->_rowset as $row) {
			$this->_optionsByTheme[$row->theme_name][$row->id] = $row->name;
		}
	}
	
	public function getOptionsByTheme($themeName) {
		return $themeName && isset($this->_optionsByTheme[$themeName]) ? $this->_optionsByTheme[$themeName] : array();
	}
	
	public function getAllOptionsByTheme() {
		return $this->_optionsByTheme;
	}
	
	public function getFirstIdByTheme($themeName) {
		$options = $this->getOptionsByTheme($themeName);
		foreach ($options as $id => $name) {
			return $id;
		}
		return null;
	}
}