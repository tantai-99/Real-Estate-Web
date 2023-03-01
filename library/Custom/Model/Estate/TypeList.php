<?php
namespace Library\Custom\Model\Estate;

use Library\Custom\Model\Estate\ClassList;
use Modules\V1api\Services;

class TypeList extends AbstractList {
	
	static protected $_instance;
	
	const TYPE_CHINTAI = 1;
	
	const TYPE_KASI_TENPO = 2;
	const TYPE_KASI_OFFICE = 3;
	const TYPE_PARKING = 4;
	const TYPE_KASI_TOCHI = 5;
	const TYPE_KASI_OTHER = 6;
	
	const TYPE_MANSION = 7;
	const TYPE_KODATE = 8;
	const TYPE_URI_TOCHI = 9;
	
	const TYPE_URI_TENPO = 10;
	const TYPE_URI_OFFICE = 11;
	const TYPE_URI_OTHER = 12;

	const COMPOSITETYPE_ALL = 1000;

	const COMPOSITETYPE_CHINTAI_JIGYO_1 = 1001;
	const COMPOSITETYPE_CHINTAI_JIGYO_2 = 1002;
	const COMPOSITETYPE_CHINTAI_JIGYO_3 = 1003;

    const COMPOSITETYPE_BAIBAI_KYOJU_1 = 1101;
    const COMPOSITETYPE_BAIBAI_KYOJU_2 = 1102;

    const COMPOSITETYPE_BAIBAI_JIGYO_1 = 1201;
    const COMPOSITETYPE_BAIBAI_JIGYO_2 = 1202;

    protected $_list = [
		self::TYPE_CHINTAI		=>'賃貸(アパート・マンション・一戸建て)',
		self::TYPE_KASI_TENPO	=>'貸店舗（テナント）',
		self::TYPE_KASI_OFFICE	=>'貸事務所（貸オフィス）',
		self::TYPE_PARKING		=>'貸駐車場',
		self::TYPE_KASI_TOCHI	=>'貸土地',
		self::TYPE_KASI_OTHER	=>'貸ビル・貸倉庫・その他',
		self::TYPE_MANSION		=>'マンション（新築・分譲・中古）',
		self::TYPE_KODATE		=>'一戸建て（新築・中古）',
		self::TYPE_URI_TOCHI	=>'売土地',
		self::TYPE_URI_TENPO	=>'売店舗',
		self::TYPE_URI_OFFICE	=>'売事務所',
		self::TYPE_URI_OTHER	=>'売ビル・売倉庫・売工場・その他',
	];
	
	protected $_url_list = [
	];
	protected $_type_by_url = [];
	
	protected $_key_consts = [];
	protected $_list_by_class = [];

	protected $_key_consts_with_composite = [];
	protected $_compositeTypes = [];
	protected $_compositePatterns = [];
	protected $_compositePatternsStr = [];
    protected $_list_composite_by_class = [];
	
	public function getKeyConst() {
		return $this->_key_consts;
	}

	public function getKeyConstWithComposite() {
	    return $this->_key_consts_with_composite;
    }
	
	public function __construct() {
		parent::__construct();
		
		$this->_list_by_class = [
			ClassList::CLASS_CHINTAI_KYOJU => [
				1,
			],
			ClassList::CLASS_CHINTAI_JIGYO => [
				2, 3, 4, 5, 6
			],
			ClassList::CLASS_BAIBAI_KYOJU => [
				7, 8, 9,
			],
			ClassList::CLASS_BAIBAI_JIGYO => [
				10, 11, 12
			],
		];
		
        $this->_list_composite_by_class = [
			ClassList::CLASS_ALL => [
				1000
			],
            ClassList::CLASS_CHINTAI_JIGYO => [
				1001,1002,1003
			],
			ClassList::CLASS_BAIBAI_KYOJU => [
				1101,1102
			],
			ClassList::CLASS_BAIBAI_JIGYO => [
				1201,1202
			],
        ];
		
		$ref = new \ReflectionClass($this);
		$consts = $ref->getConstants();
		
		foreach ($consts as $const => $value) {
			if (strpos($const, 'TYPE_') === 0) {
                $this->_key_consts[$const] = $value;
                $this->_key_consts_with_composite[$const] = $value;
				$this->_url_list[$value] = implode('-', explode('_', strtolower( str_replace('TYPE_', '', $const) )));
			} elseif (strpos($const, 'COMPOSITETYPE_') === 0) {
                $this->_key_consts_with_composite[$const] = $value;
                $this->_url_list[$value] = implode('-', explode('_', strtolower( str_replace('COMPOSITETYPE_', '', $const) )));
            }
		}
		
		$this->_type_by_url = array_flip($this->_url_list);

		// 複合タイプ
        $this->_compositeTypes = [
            self::TYPE_KASI_TENPO,
            self::TYPE_KASI_OFFICE,
            self::TYPE_PARKING,
            self::TYPE_KASI_TOCHI,
            self::TYPE_KASI_OTHER,

            self::TYPE_MANSION,
            self::TYPE_KODATE,
            self::TYPE_URI_TOCHI,

            self::TYPE_URI_TENPO,
            self::TYPE_URI_OFFICE,
            self::TYPE_URI_OTHER,
        ];

        $this->_compositePatterns = [
			[self::COMPOSITETYPE_ALL , [1, 1, 1, 1, 1,    1, 1, 1,    1, 1, 1]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_1, [1, 1, 0, 0, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 1, 0, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 0, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_2, [1, 0, 0, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 1, 0, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 0, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_2, [0, 1, 0, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 0, 1, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 0, 1, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 0, 0, 1, 1,    0, 0, 0,    0, 0, 0]],

            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 1, 0, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 0, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_2, [1, 1, 0, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 1, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 1, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 0, 1, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 1, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 1, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 0, 1, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 0, 1, 1, 1,    0, 0, 0,    0, 0, 0]],

            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 1, 1, 0,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 1, 0, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 0, 1, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 0, 1, 1, 1,    0, 0, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [0, 1, 1, 1, 1,    0, 0, 0,    0, 0, 0]],

            [self::COMPOSITETYPE_CHINTAI_JIGYO_3, [1, 1, 1, 1, 1,    0, 0, 0,    0, 0, 0]],

            [self::COMPOSITETYPE_BAIBAI_KYOJU_1, [0, 0, 0, 0, 0,    1, 1, 0,    0, 0, 0]],
            [self::COMPOSITETYPE_BAIBAI_KYOJU_2, [0, 0, 0, 0, 0,    1, 0, 1,    0, 0, 0]],
            [self::COMPOSITETYPE_BAIBAI_KYOJU_2, [0, 0, 0, 0, 0,    0, 1, 1,    0, 0, 0]],
            [self::COMPOSITETYPE_BAIBAI_KYOJU_2, [0, 0, 0, 0, 0,    1, 1, 1,    0, 0, 0]],

            [self::COMPOSITETYPE_BAIBAI_JIGYO_1, [0, 0, 0, 0, 0,    0, 0, 0,    1, 1, 0]],
            [self::COMPOSITETYPE_BAIBAI_JIGYO_2, [0, 0, 0, 0, 0,    0, 0, 0,    1, 0, 1]],
            [self::COMPOSITETYPE_BAIBAI_JIGYO_2, [0, 0, 0, 0, 0,    0, 0, 0,    0, 1, 1]],
            [self::COMPOSITETYPE_BAIBAI_JIGYO_2, [0, 0, 0, 0, 0,    0, 0, 0,    1, 1, 1]],
        ];

		foreach ($this->_compositePatterns as $pattern) {
		    $this->_compositePatternsStr[implode(',', $pattern[1])] = $pattern[0];
        }
	}
	
	// 賃貸か売買か
	public function isRent($class) {
        if ($class == ClassList::CLASS_CHINTAI_KYOJU ||
            $class == ClassList::CLASS_CHINTAI_JIGYO)
        {
            return true;
        }
        return false;
	}

	/**
	* 賃貸種別の種目を含んでいるか
	*/
	public function containsRent($types) {
		return $this->containsClass($types, [
			ClassList::CLASS_CHINTAI_KYOJU,
			ClassList::CLASS_CHINTAI_JIGYO
		]);
	}

	/**
	* 売買種別の種目を含んでいるか
	*/
	public function containsPurchase($types) {
		return $this->containsClass($types, [
			ClassList::CLASS_BAIBAI_KYOJU,
			ClassList::CLASS_BAIBAI_JIGYO
		]);
	}

	/**
	* 指定種別の種目を含んでいるか
	*/
	public function containsClass($types, $classes) {
		foreach ($types as $type) {
			if (in_array($this->getClassByType($type), $classes, true)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 種目urlを取得する
	 */
	public function getUrl($key) {
		return isset($this->_url_list[$key])?$this->_url_list[$key]:null;
	}
	
	/**
	 * 種目urlを全て取得する
	 */
	public function getUrlAll() {
		return $this->_url_list;
	}
	
	/**
	 * URLを指定して種目IDを取得する
	 */
	public function getTypeByUrl($url) {
		return isset($this->_type_by_url[$url]) ? $this->_type_by_url[$url] : null;
	}
	
	/**
	 * URLを指定して種目が属する種別IDを取得する
	 */
	public function getClassByUrl($url) {
		return $this->getClassByType( $this->getTypeByUrl($url) );
	}
	
	/**
	 * URL毎の種目IDマップを取得する
	 */
	public function getTypeByUrlAll() {
		return $this->_type_by_url;
	}
	
	/**
	 * 種別IDに属する種目IDを全て取得する
	 */
	public function getByClass($class) {
		if (!isset($this->_list_by_class[$class])) {
			return [];
		}
		return $this->pick($this->_list_by_class[$class]);
	}
	
	/**
	 * 種目IDの属する種別IDを取得する
	 */
	public function getClassByType($type) {
		$type = (int) $type;
		foreach ($this->_list_by_class as $class => $types) {
			if (in_array($type, $types, true)) {
				return $class;
			}
		}
		return null;
	}
	
	public static function getShumokuCode($type) {
		switch ($type)
		{
			case self::TYPE_CHINTAI:
				return '5007';
			case self::TYPE_KASI_TENPO:
				return '5008';
			case self::TYPE_KASI_OFFICE:
				return '5009';
			case self::TYPE_PARKING:
				return '5011';
			case self::TYPE_KASI_TOCHI:
				return '5012';
			case self::TYPE_KASI_OTHER:
				return '5010';
			case self::TYPE_MANSION:
				return '5003';
			case self::TYPE_KODATE:
				return '5002';
			case self::TYPE_URI_TOCHI:
				return '5001';
			case self::TYPE_URI_TENPO:
				return '5004';
			case self::TYPE_URI_OFFICE:
				return '5005';
			case self::TYPE_URI_OTHER:
				return '5006';
		}
		return null;
	}

	public function getByShumokuCode($code) {
		switch ($code)
		{
			case '5007':
				return self::TYPE_CHINTAI;
			case '5008':
				return self::TYPE_KASI_TENPO;
			case '5009':
				return self::TYPE_KASI_OFFICE;
			case '5011':
				return self::TYPE_PARKING;
			case '5012':
				return self::TYPE_KASI_TOCHI;
			case '5010':
				return self::TYPE_KASI_OTHER;
			case '5003':
				return self::TYPE_MANSION;
			case '5002':
				return self::TYPE_KODATE;
			case '5001':
				return self::TYPE_URI_TOCHI;
			case '5004':
				return self::TYPE_URI_TENPO;
			case '5005':
				return self::TYPE_URI_OFFICE;
			case '5006':
				return self::TYPE_URI_OTHER;
		}
		return null;
	}

    /**
     * 複合タイプを取得する　種目が単一の場合、種目IDを返す
     * @param array|int $types
     * @return int
     */
	public function getCompositeType($types) {
	    if (!is_array($types)) {
	        return $types;
        }
	    if (count($types) === 1) {
	        return $types[0];
        }
        foreach ($types as $key => $val) {
            $types[$key] = (int) $val;
        }

        $pattern = [];
	    foreach ($this->_compositeTypes as $type) {
	        $pattern[] = in_array($type, $types, true) ? 1 : 0;
        }
        $patternStr = implode(',', $pattern);
        if (!isset($this->_compositePatternsStr[$patternStr])) {
            throw new \Exception('値が不正です。');
		}
        return $this->_compositePatternsStr[$patternStr];
    }

    /**
     * 物件API種目コードから複合種目IDを取得する
     * @param $codes
     */
    public function getComopsiteTypeByShumokuCd($codes) {
        $types = [];
        foreach ($codes as $cd) {
            $ct = Services\ServiceUtils::getShumokuCtByCd($cd);
            $types[] = $this->getTypeByUrl($ct);
        }
        return $this->getCompositeType($types);
    }

    /**
	 * get class comopsite by url
	 */
	public function getClassByUrlComopsite($url) {
		$type = (int) $this->getTypeByUrl($url);
		foreach ($this->_list_composite_by_class as $class => $types) {
			if (in_array($type, $types, true)) {
				return $class;
			}
		}
		return null;
    }

}