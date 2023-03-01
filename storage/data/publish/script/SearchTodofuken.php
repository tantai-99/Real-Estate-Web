<?php

class SearchTodofuken {

    // 県コード
    // 物件APIの ken_cd に同じ
    const HOKKAIDO  = '01';
    const AOMORI    = '02';
    const IWATE     = '03';
    const MIYAGI    = '04';
    const AKITA     = '05';
    const YAMAGATA  = '06';
    const FUKUSHIMA = '07';
    const IBARAKI   = '08';
    const TOCHIGI   = '09';
    const GUNMA     = '10';
    const SAITAMA   = '11';
    const CHIBA     = '12';
    const TOKYO     = '13';
    const KANAGAWA  = '14';
    const NIIGATA   = '15';
    const TOYAMA    = '16';
    const ISHIKAWA  = '17';
    const FUKUI     = '18';
    const YAMANASHI = '19';
    const NAGANO    = '20';
    const GIFU      = '21';
    const SHIZUOKA  = '22';
    const AICHI     = '23';
    const MIE       = '24';
    const SHIGA     = '25';
    const KYOTO     = '26';
    const OSAKA     = '27';
    const HYOGO     = '28';
    const NARA      = '29';
    const WAKAYAMA  = '30';
    const TOTTORI   = '31';
    const SHIMANE   = '32';
    const OKAYAMA   = '33';
    const HIROSHIMA = '34';
    const YAMAGUCHI = '35';
    const TOKUSHIMA = '36';
    const KAGAWA    = '37';
    const EHIME     = '38';
    const KOCHI     = '39';
    const FUKUOKA   = '40';
    const SAGA      = '41';
    const NAGASAKI  = '42';
    const KUMAMOTO  = '43';
    const OITA      = '44';
    const MIYAZAKI  = '45';
    const KAGOSHIMA = '46';
    const OKINAWA   = '47';

    // 地域コード
    const AREA_HOKKAIDO_TOHOKU   = 1;
    const AREA_SHUTOKEN          = 2;
    const AREA_SHINETSU_HOKURIKU = 3;
    const AREA_TOKAI             = 4;
    const AREA_KINKI             = 5;
    const AREA_CHUGOKU_SHIKOKU   = 6;
    const AREA_KYUSHU_OKINAWA    = 7;

    static public function dirname_all() {

        return [
            self::HOKKAIDO  => 'hokkaido',
            self::AOMORI    => 'aomori',
            self::IWATE     => 'iwate',
            self::MIYAGI    => 'miyagi',
            self::AKITA     => 'akita',
            self::YAMAGATA  => 'yamagata',
            self::FUKUSHIMA => 'fukushima',
            self::IBARAKI   => 'ibaraki',
            self::TOCHIGI   => 'tochigi',
            self::GUNMA     => 'gunma',
            self::SAITAMA   => 'saitama',
            self::CHIBA     => 'chiba',
            self::TOKYO     => 'tokyo',
            self::KANAGAWA  => 'kanagawa',
            self::NIIGATA   => 'niigata',
            self::TOYAMA    => 'toyama',
            self::ISHIKAWA  => 'ishikawa',
            self::FUKUI     => 'fukui',
            self::YAMANASHI => 'yamanashi',
            self::NAGANO    => 'nagano',
            self::GIFU      => 'gifu',
            self::SHIZUOKA  => 'shizuoka',
            self::AICHI     => 'aichi',
            self::MIE       => 'mie',
            self::SHIGA     => 'shiga',
            self::KYOTO     => 'kyoto',
            self::OSAKA     => 'osaka',
            self::HYOGO     => 'hyogo',
            self::NARA      => 'nara',
            self::WAKAYAMA  => 'wakayama',
            self::TOTTORI   => 'tottori',
            self::SHIMANE   => 'shimane',
            self::OKAYAMA   => 'okayama',
            self::HIROSHIMA => 'hiroshima',
            self::YAMAGUCHI => 'yamaguchi',
            self::TOKUSHIMA => 'tokushima',
            self::KAGAWA    => 'kagawa',
            self::EHIME     => 'ehime',
            self::KOCHI     => 'kochi',
            self::FUKUOKA   => 'fukuoka',
            self::SAGA      => 'saga',
            self::NAGASAKI  => 'nagasaki',
            self::KUMAMOTO  => 'kumamoto',
            self::OITA      => 'oita',
            self::MIYAZAKI  => 'miyazaki',
            self::KAGOSHIMA => 'kagoshima',
            self::OKINAWA   => 'okinawa',
        ];
    }

    static public function japanese_all() {

        return [
            self::HOKKAIDO  => '北海道',
            self::AOMORI    => '青森県',
            self::IWATE     => '岩手県',
            self::MIYAGI    => '宮城県',
            self::AKITA     => '秋田県',
            self::YAMAGATA  => '山形県',
            self::FUKUSHIMA => '福島県',
            self::IBARAKI   => '茨城県',
            self::TOCHIGI   => '栃木県',
            self::GUNMA     => '群馬県',
            self::SAITAMA   => '埼玉県',
            self::CHIBA     => '千葉県',
            self::TOKYO     => '東京都',
            self::KANAGAWA  => '神奈川県',
            self::NIIGATA   => '新潟県',
            self::TOYAMA    => '富山県',
            self::ISHIKAWA  => '石川県',
            self::FUKUI     => '福井県',
            self::YAMANASHI => '山梨県',
            self::NAGANO    => '長野県',
            self::GIFU      => '岐阜県',
            self::SHIZUOKA  => '静岡県',
            self::AICHI     => '愛知県',
            self::MIE       => '三重県',
            self::SHIGA     => '滋賀県',
            self::KYOTO     => '京都府',
            self::OSAKA     => '大阪府',
            self::HYOGO     => '兵庫県',
            self::NARA      => '奈良県',
            self::WAKAYAMA  => '和歌山県',
            self::TOTTORI   => '鳥取県',
            self::SHIMANE   => '島根県',
            self::OKAYAMA   => '岡山県',
            self::HIROSHIMA => '広島県',
            self::YAMAGUCHI => '山口県',
            self::TOKUSHIMA => '徳島県',
            self::KAGAWA    => '香川県',
            self::EHIME     => '愛媛県',
            self::KOCHI     => '高知県',
            self::FUKUOKA   => '福岡県',
            self::SAGA      => '佐賀県',
            self::NAGASAKI  => '長崎県',
            self::KUMAMOTO  => '熊本県',
            self::OITA      => '大分県',
            self::MIYAZAKI  => '宮崎県',
            self::KAGOSHIMA => '鹿児島県',
            self::OKINAWA   => '沖縄県',
        ];
    }

    static public function area_japanese_all() {

        return [
            self::AREA_HOKKAIDO_TOHOKU   => '北海道・東北',
            self::AREA_SHUTOKEN          => '首都圏',
            self::AREA_SHINETSU_HOKURIKU => '信越・北陸',
            self::AREA_TOKAI             => '東海',
            self::AREA_KINKI             => '近畿',
            self::AREA_CHUGOKU_SHIKOKU   => '中国・四国',
            self::AREA_KYUSHU_OKINAWA    => '九州・沖縄',
        ];
    }

    static public function category_map() {

        return [
            self::AREA_HOKKAIDO_TOHOKU   => [
                self::HOKKAIDO,
                self::AOMORI,
                self::IWATE,
                self::MIYAGI,
                self::AKITA,
                self::YAMAGATA,
                self::FUKUSHIMA,
            ],
            self::AREA_SHUTOKEN          => [
                self::TOKYO,
                self::KANAGAWA,
                self::CHIBA,
                self::SAITAMA,
                self::IBARAKI,
                self::TOCHIGI,
                self::GUNMA,
                self::YAMANASHI,
            ],
            self::AREA_SHINETSU_HOKURIKU => [
                self::NIIGATA,
                self::NAGANO,
                self::TOYAMA,
                self::ISHIKAWA,
                self::FUKUI,
            ],
            self::AREA_TOKAI             => [
                self::AICHI,
                self::GIFU,
                self::MIE,
                self::SHIZUOKA,
            ],
            self::AREA_KINKI             => [
                self::SHIGA,
                self::KYOTO,
                self::OSAKA,
                self::HYOGO,
                self::NARA,
                self::WAKAYAMA,
            ],
            self::AREA_CHUGOKU_SHIKOKU   => [
                self::TOTTORI,
                self::SHIMANE,
                self::OKAYAMA,
                self::HIROSHIMA,
                self::YAMAGUCHI,
                self::TOKUSHIMA,
                self::KAGAWA,
                self::EHIME,
                self::KOCHI,

            ],
            self::AREA_KYUSHU_OKINAWA    => [
                self::FUKUOKA,
                self::SAGA,
                self::NAGASAKI,
                self::KUMAMOTO,
                self::OITA,
                self::MIYAZAKI,
                self::KAGOSHIMA,
                self::OKINAWA,
            ],
        ];
    }

    static public function code_by_dirname($dirname) {

        return array_search($dirname, self::dirname_all());
    }

    static public function code_all() {

        return array_keys(self::japanese_all());
    }

    static public function area_code_all() {

        return array_keys(self::area_code_all());
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

    static public function area_japanese_by_code($area_code) {

        return self::area_japanese_all()[$area_code];
    }

}