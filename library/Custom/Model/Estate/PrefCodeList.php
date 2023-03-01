<?php
namespace Library\Custom\Model\Estate;

use App\Repositories\Mpref\MprefRepositoryInterface;
use App;

class PrefCodeList extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [];
    protected $_list_with_suffix = [];
	protected $_list_by_area = [];
	protected $_list_by_url = [];
	protected $_list_by_url_with_suffix = [];
	protected $_url_list = [];
	protected $_code_by_url = [];
	protected $_area_list = [];

	public function __construct() {
		parent::__construct();
		
		$rowset = App::make(MprefRepositoryInterface::class)->fetchAll();
		foreach ($rowset->toArray() as $row) {
			$this->_list[ $row['pref_code'] ] = $row['pref_name'];
            $this->_list_with_suffix[ $row['pref_code'] ] = $row['pref_name'].$this->getPrefSuffixByCode($row['pref_code']);
			$this->_list_by_area[ $row['area_category_id'] ][] = $row['pref_code'];
			
			$this->_list_by_url[$row['pref_url']] = $row['pref_name'];
            $this->_list_by_url_with_suffix[$row['pref_url']] = $row['pref_name'].$this->getPrefSuffixByUrl($row['pref_url']);
			$this->_url_list[$row['pref_code']] = $row['pref_url'];
			$this->_code_by_url = array_flip($this->_url_list);
			$this->_area_list[$row['pref_code']] = $row['area_category_id'];
		}
	}

	public function getArea($prefCode) {
		return isset($this->_area_list[$prefCode]) ? $this->_area_list[$prefCode]: null;
	}

	public function getByArea($areaCategory) {
		if (!isset($this->_list_by_area[$areaCategory])) {
			return [];
		}
		return $this->pick($this->_list_by_area[$areaCategory]);
	}
	
	
	/**
	 * 都道府県コードを指定してurlを取得する
	 * @param string $prefCode
	 */
	public function getUrl($prefCode) {
		return isset($this->_url_list[$prefCode]) ? $this->_url_list[$prefCode]: null;
	}
	
	/**
	 * urlを全て取得する
	 */
	public function getUrlAll() {
		return $this->_url_list;
	}
	
	/**
	 * urlを指定して都道府県名を取得する
	 * @param string $url
	 */
	public function getNameByUrl($url,$withSuffix=false) {

        if($withSuffix==true){
            return isset($this->_list_by_url_with_suffix[$url]) ? $this->_list_by_url_with_suffix[$url]: null;
        }

		return isset($this->_list_by_url[$url]) ? $this->_list_by_url[$url]: null;
	}
	
	/**
	 * url毎の都道府県名を全て取得する
	 */
	public function getNameByUrlAll($withSuffix=false) {

	    if($withSuffix==true){
	        return $this->_list_by_url_with_suffix;
        }
		return $this->_list_by_url;
	}
	
	/**
	 * urlを指定して都道府県コードを取得する
	 * @param string $url
	 */
	public function getCodeByUrl($url) {
		return isset($this->_code_by_url[$url]) ? $this->_code_by_url[$url]: null;
	}
	
	/**
	 * url毎の都道府県コードを取得する
	 */
	public function getCodeByUrlAll() {
		return $this->_code_by_url;
	}

	private function getPrefSuffixByCode($pref_code){
	    $suffix = "";
	    switch($pref_code){
	        case '01':
	            break;
            case '13':
                $suffix = "都";
                break;
            case '26':
            case '27':
                $suffix = "府";
                break;
            default :
                $suffix = "県";
                break;
        }
	    return $suffix;

    }

    private function getPrefSuffixByUrl($url){
        $suffix = "";
        switch($url){
            case 'hokkaido':
                break;
            case 'tokyo':
                $suffix = "都";
                break;
            case 'kyoto':
            case 'osaka':
                $suffix = "府";
                break;
            default :
                $suffix = "県";
                break;
        }
        return $suffix;
    }

}
