<?php
namespace Library\Custom\Model\Estate;

use App;
use App\Repositories\MAreaCategory\MAreaCategoryRepositoryInterface;

class AreaCategoryList extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [];
	
	public function __construct() {
		parent::__construct();
		
		$rowset = App::make(MAreaCategoryRepositoryInterface::class)->fetchAll();
		foreach ($rowset->toArray() as $row) {
			$this->_list[ $row['id'] ] = $row['area_name'];
		}
	}
}