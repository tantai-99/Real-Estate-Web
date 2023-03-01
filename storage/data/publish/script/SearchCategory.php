<?php

require_once('SearchShumoku.php');

class SearchCategory {

    const CATEGORY_KR = 1;
    const CATEGORY_JR = 2;
    const CATEGORY_KS = 3;
    const CATEGORY_JS = 4;

    static public function category_all() {

        return [
            self::CATEGORY_KR => 'kr',
            self::CATEGORY_JR => 'jr',
            self::CATEGORY_KS => 'ks',
            self::CATEGORY_JS => 'js',
        ];
    }

    static public function japanese_all() {

        return [
            self::CATEGORY_KR => '賃貸マンション・アパート・一戸建て',
            self::CATEGORY_JR => '賃貸 駐車場/店舗/事務所/ビル・倉庫・その他',
            self::CATEGORY_KS => '売マンション/一戸建て/土地',
            self::CATEGORY_JS => '売店舗/事務所/ビル・一括マンション・その他',
        ];
    }

    static public function category_map() {

        return [
            self::CATEGORY_KR => [
                SearchShumoku::CHINTAI,
            ],
            self::CATEGORY_JR => [
                SearchShumoku::KASI_TENPO,
                SearchShumoku::KASI_OFFICE,
                SearchShumoku::PARKING,
                SearchShumoku::KASI_TOCHI,
                SearchShumoku::KASI_OTHER,
            ],
            self::CATEGORY_KS => [
                SearchShumoku::MANSION,
                SearchShumoku::KODATE,
                SearchShumoku::URI_TOCHI,
            ],
            self::CATEGORY_JS => [
                SearchShumoku::URI_TENPO,
                SearchShumoku::URI_OFFICE,
                SearchShumoku::URI_OTHER,
            ],
        ];
    }

    /**
     * テンプレート名 取得
     *
     * @param $shumoku_ct
     * @return mixed
     */
    static public function getTemplateName($shumoku_ct) {

        $shumoku_code  = SearchShumoku::code_by_dirname($shumoku_ct);
        $category_code = self::getCodeByShumokuCode($shumoku_code);
        return self::getCategoryCtByCode($category_code);
    }

    /**
     * カテゴリーコード 取得
     *
     * @param $code
     * @return int|null|string
     */
    static private function getCodeByShumokuCode($code) {

        foreach (self::category_map() as $category_code => $array) {
            foreach ($array as $shumoku_code) {
                if ($shumoku_code === $code) {
                    return $category_code;
                }
            }
        }
        return null;
    }

    /**
     * カテゴリー名 取得
     *
     * @param $code
     * @return mixed
     */
    static private function getCategoryCtByCode($code) {

        return self::category_all()[$code];
    }
}