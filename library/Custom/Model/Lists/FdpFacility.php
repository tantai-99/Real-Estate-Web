<?php
namespace Library\Custom\Model\Lists;

class FdpFacility extends ListAbstract {
    
    static protected $_instance;

    const TYPE_DEPARTMENT           = 'department';
    const TYPE_SUPERMAKET           = 'supermaket';
    const TYPE_CONVIENCESTORE       = 'conviencestore';
    const TYPE_DISCOUNTSTORE        = 'discountstore';
    const TYPE_DRUGSTORE            = 'drugstore';
    const TYPE_PARK                 = 'park';
    const TYPE_RESTAURENT           = 'restaurent';
    const TYPE_SHOPPING             = 'shopping';
    const TYPE_NURSERYSCHOOL        = 'nurseryschool';
    const TYPE_HIGHSCHOOL           = 'highschool';
    const TYPE_UNIVERSITY           = 'university';
    const TYPE_PUBLIC               = 'public';
    const TYPE_FINANCIALPOSTOFFICES = 'financial';
    const TYPE_HOSPITAL             = 'hospital';
    // const TYPE_CARECENTER           = 'carecenter';
    const TYPE_BUSSTOP              = 'busstop';
    const TYPE_COINPARKING          = 'coinparking';
    const TYPE_CAR                  = 'car';

    public function listFacilityName() {
        return array(
            self::TYPE_SUPERMAKET               => 'スーパー',
            self::TYPE_CONVIENCESTORE           => 'コンビニ',
            self::TYPE_DRUGSTORE                => 'ドラッグストア',
            self::TYPE_DEPARTMENT               => 'ショッピングモール・デパート',
            self::TYPE_SHOPPING                  => 'ショッピング施設',
            self::TYPE_RESTAURENT               => '飲食店',
            self::TYPE_PARK                     => '公園',
            self::TYPE_COINPARKING              => 'コインパーキング',
            self::TYPE_BUSSTOP                  => 'バス停',
            self::TYPE_CAR                      => 'カーシェア・レンタカー',
            self::TYPE_NURSERYSCHOOL            => '幼稚園・保育園',
            self::TYPE_HIGHSCHOOL               => '小学校・中学校',
            self::TYPE_UNIVERSITY               => '高校・大学・専門学校等',
            self::TYPE_HOSPITAL                 => '病院',
            self::TYPE_FINANCIALPOSTOFFICES     => '金融機関・郵便局',
            self::TYPE_PUBLIC                   => '公共施設',
        );
    }

    public function listFacilityDisplayBegin() {
        return array(
            self::TYPE_SUPERMAKET,
            self::TYPE_CONVIENCESTORE,
            self::TYPE_DRUGSTORE,
            self::TYPE_DEPARTMENT,
            self::TYPE_PARK,
            self::TYPE_COINPARKING,
            self::TYPE_BUSSTOP,
            self::TYPE_HOSPITAL,
        );
    }

    public function getCategoryApi() {
        return array(
            self::TYPE_DEPARTMENT => 'contents/ipc/poi.geojson/2339:2342:2340/',
            self::TYPE_SUPERMAKET => 'contents/ipc/poi.geojson/2493/',
            self::TYPE_CONVIENCESTORE => 'contents/ipc/poi.geojson/2354/',
            self::TYPE_DISCOUNTSTORE => 'contents/ipc/poi.geojson/2343:2920/',
            self::TYPE_DRUGSTORE => 'contents/ipc/poi.geojson/2891/',
        );
    }
}