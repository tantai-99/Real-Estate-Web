<?php
namespace Library\Custom\Model\Master;

use App\Repositories\MTheme\MThemeRepositoryInterface;
use Illuminate\Support\Facades\App;

class Theme extends MasterAbstract {
	/**
	 * @var Library\Custom\Model\Master\Theme
	 */
	static protected $_instance;
	protected $_table;

	function __construct() {
		
		$this->_table =App::make(MThemeRepositoryInterface::class);
		parent::__construct();
	}

	public function getRowsetByGroup() {
		$rowset = $this->getRowset();
		$result = array();

		$i = 0;
		foreach ($rowset as $row) {
			$result[floor($i/6)][] = $row;

			$i++;
		}
		return $result;
	}

	/**
	 * テーマを取得
	 *
	 */
	public function getThemeRowsetByGroup($company_id) {

		$model = new $this->_table();
		$this->i      = 0;
		$this->result = [];
		if (config('environment.theme.display_all')) {
			// カスタムテーマを含むすべてのテーマを強制的に表示
			$this->makeResponse($model->fetchAllTheme());
		}
		else {
			// カスタム
			$this->makeResponse($model->getCustomData($company_id));
			// デフォルト
			$this->makeResponse($model->getNomalData());
		}
		return $this->result;
	}

	private $i;
	private $result;

	private function makeResponse($rowset) {

		if (count($rowset) > 0) {
			foreach ($rowset as $row) {
				$this->result[floor($this->i / 6)][] = $row;
				$this->i++;
			}
		}
	}


	/**
	 * 標準のテーマを取得する
	 *
	 */
	public function getNormalRowsetByGroup() {

		$obj = new $this->_tableClass();
		$rowset = $obj->getNomalData();
		$result = array();

		$i = 0;
		foreach ($rowset as $row) {
			$result[floor($i/6)][] = $row;

			$i++;
		}
		return $result;
	}
	/**
	 * 店舗別のカスタムテーマを取得する
	 *
	 */
	public function getCustomRowsetByGroup($company_id) {

		$obj = new $this->_table;
		$rowset = $obj->getCustomData($company_id);
		$result = array();
		$i = 0;
		foreach ($rowset as $row) {
			$result[floor($i/6)][] = $row;

			$i++;
		}
		return $result;
	}

}