<?php

class SearchShumoku {

    // 物件種目コード
    const CHINTAI     = 1;
    const KASI_TENPO  = 2;
    const KASI_OFFICE = 3;
    const PARKING     = 4;
    const KASI_TOCHI  = 5;
    const KASI_OTHER  = 6;
    const MANSION     = 7;
    const KODATE      = 8;
    const URI_TOCHI   = 9;
    const URI_TENPO   = 10;
    const URI_OFFICE  = 11;
    const URI_OTHER   = 12;
    const ALL         = 13;
    const CHINTAI_JIGYO_1 = 1001;
    const CHINTAI_JIGYO_2 = 1002;
    const CHINTAI_JIGYO_3 = 1003;
    const BAIBAI_KYOJU_1 = 1101;
    const BAIBAI_KYOJU_2 = 1102;
    const BAIBAI_JIGYO_1 = 1201;
    const BAIBAI_JIGYO_2 = 1202;

    static public function dirname_all() {

        return [
            self::CHINTAI     => 'chintai',
            self::KASI_TENPO  => 'kasi-tenpo',
            self::KASI_OFFICE => 'kasi-office',
            self::PARKING     => 'parking',
            self::KASI_TOCHI  => 'kasi-tochi',
            self::KASI_OTHER  => 'kasi-other',
            self::MANSION     => 'mansion',
            self::KODATE      => 'kodate',
            self::URI_TOCHI   => 'uri-tochi',
            self::URI_TENPO   => 'uri-tenpo',
            self::URI_OFFICE  => 'uri-office',
            self::URI_OTHER   => 'uri-other',
        ];
    }

    static public function dirname_freeword() {
        return [
            self::ALL               => 'all',
            self::CHINTAI           => 'chintai',

            self::KASI_TENPO        => 'kasi-tenpo',
            self::KASI_OFFICE       => 'kasi-office',
            self::PARKING           => 'parking',
            self::KASI_TOCHI        => 'kasi-tochi',
            self::CHINTAI_JIGYO_1   => 'chintai-jigyo-1',
            self::CHINTAI_JIGYO_2   => 'chintai-jigyo-2',
            self::CHINTAI_JIGYO_3   => 'chintai-jigyo-3',

            self::MANSION           => 'mansion',
            self::KODATE            => 'kodate',
            self::URI_TOCHI         => 'uri-tochi',
            self::BAIBAI_KYOJU_1    => 'baibai-kyoju-1',
            self::BAIBAI_KYOJU_2    => 'baibai-kyoju-2',

            self::URI_TENPO         => 'uri-tenpo',
            self::URI_OFFICE        => 'uri-office',
            self::URI_OTHER         => 'uri-other',
            self::BAIBAI_JIGYO_1    => 'baibai-jigyo-1',
            self::BAIBAI_JIGYO_2    => 'baibai-jigyo-2',
        ];
    }

    static public function japanese_all() {

        return [
            self::CHINTAI     => '賃貸（アパート・マンション・一戸建て）',
            self::KASI_TENPO  => '貸店舗（テナント）',
            self::KASI_OFFICE => '貸事務所（貸オフィス）',
            self::PARKING     => '貸駐車場',
            self::KASI_TOCHI  => '貸土地',
            self::KASI_OTHER  => '賃ビル・賃倉庫・その他',
            self::MANSION     => 'マンション（新築・分譲・中古）',
            self::KODATE      => '一戸建て（新築・中古）',
            self::URI_TOCHI   => '売土地',
            self::URI_TENPO   => '売店舗',
            self::URI_OFFICE  => '売事務所',
            self::URI_OTHER   => '売ビル・売倉庫・売工場・その他',
        ];
    }

    static public function code_by_dirname($dirname) {

        return array_search($dirname, self::dirname_all());
    }

    static public function code_all() {

        return array_keys(self::japanese_all());
    }

    static public function dirname_by_code($code) {

        return self::dirname_all()[$code];
    }

    static public function japanese_by_code($code) {

        return self::japanese_all()[$code];
    }

    static public function japanese_by_dirname($dirname) {

        return self::japanese_by_code(array_search($dirname, self::dirname_all()));
    }

}