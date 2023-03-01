<?php
namespace Library\Custom\Publish\Estate\Make;

use Library\Custom\Model\Estate;

abstract class AbstractMake {

    private static $instance;

    private function __construct() {
    }

    public static function getInstance() {

        if (!self::$instance) self::$instance = new static;
        return self::$instance;
    }

    final function __clone() {

        throw new \Exception('Clone is not allowed against'.get_class($this));
    }

    protected $hp;
    public    $estateSetting;
    public    $searchSettingRowset;
    public    $estateTypes;
    public    $hasRentEstateTypes;
    public    $hasPurchaseEstateTypes;

    public function init($hp) {

        // reset property
        foreach (get_object_vars($this) as $property => $value) {
            $this->$property = null;
        }

        $this->hp                  = $hp;
        $this->estateSetting       = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $this->searchSettingRowset = $this->estateSetting instanceof \App\Models\HpEstateSetting ? $this->estateSetting->getSearchSettingAll() : [];

        $this->estateTypes = [];
        if (!$this->searchSettingRowset instanceof \App\Collections\EstateClassSearchCollection) {
            return;
        }
        $this->estateTypes = $this->searchSettingRowset->getEstateTypes();

        // 賃貸・売買種別の種目の設定があるか
        $typeList = Estate\TypeList::getInstance();
        $this->hasRentEstateTypes = $typeList->containsRent($this->estateTypes);
        $this->hasPurchaseEstateTypes = $typeList->containsPurchase($this->estateTypes);
    }

    public function getMap() {

        $res = [];

        $table = Estate\TypeList::getInstance();

        foreach ($this->estateTypes as $type) {

            $class = (int)$table->getClassByType($type);

            $rent_or_purchase = null;

            if ($class === Estate\ClassList::CLASS_CHINTAI_KYOJU || //
                $class === Estate\ClassList::CLASS_CHINTAI_JIGYO
            ) {
                $rent_or_purchase = Estate\ClassList::RENT;
            }

            if ($class === Estate\ClassList::CLASS_BAIBAI_KYOJU || //
                $class === Estate\ClassList::CLASS_BAIBAI_JIGYO
            ) {
                $rent_or_purchase = Estate\ClassList::PURCHASE;
            }

            $res[$rent_or_purchase][$class][$type] = $table->get($type);
        }

        return $res;
    }
}