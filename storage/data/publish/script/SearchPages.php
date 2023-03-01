<?php

class SearchPages {

    // ページコード
    const SHUMOKU                           = 1; //  物件種目選択
    const RENT                              = 2; //  賃貸物件種目選択
    const PURCHASE                          = 3; //  売買物件種目選択
    const SELECT_PREFECTURE                 = 4; //  都道府県選択（物件種目トップ）
    const SELECT_CITY                       = 5; //  市区選択
    const SELECT_RAILWAY                    = 6; //  沿線選択
    const SELECT_STATION                    = 7; //  駅選択
    const SELECT_STATION_MULTI_RAYWAY       = 8; //  駅選択（複数路線選択時）
    const RESULT_PREFECTURE                 = 9; //  都道府県物件一覧
    const RESULT_AREA                       = 10; //  エリアの物件一覧
    const RESULT_AREA_MULTI                 = 11; //  エリアの物件一覧（複数）
    const RESULT_MCITY                      = 12; //  政令指定都市からの物件一覧
    const RESULT_STATION                    = 13; //  駅からの物件一覧
    const RESULT_STATION_MULTI              = 14; //  沿線・駅の物件一覧（複数）
    const RESULT_RAILWAY                    = 15; //  沿線の物件一覧
    const DETAIL                            = 16; //  物件詳細
    const DETAIL_MAP                        = 17; //  物件詳細の周辺環境
    const DETAIL_CLOSED                     = 18; //  物件詳細 公開終了物件
    const FAVORITE                          = 19; //  お気に入り
    const HISTORY                           = 20; //  最近見た物件
    const KASI_JIGYOU_COMPLETE              = 21; //  賃貸事業用 完了画面
    const KASI_JIGYOU_CONFIRM               = 22; //  賃貸事業用 確認画面
    const KASI_JIGYOU_EDIT                  = 23; //  賃貸事業用 編集画面
    const KASI_KYOJUU_COMPLETE              = 24; //  賃貸居住用 完了画面
    const KASI_KYOJUU_CONFIRM               = 25; //  賃貸居住用 確認画面
    const KASI_KYOJUU_EDIT                  = 26; //  賃貸居住用 編集画面
    const URI_JIGYOU_COMPLETE               = 27; //  売買事業用 完了画面
    const URI_JIGYOU_CONFIRM                = 28; //  売買事業用 確認画面
    const URI_JIGYOU_EDIT                   = 29; //  売買事業用 編集画面
    const URI_KYOJUU_COMPLETE               = 30; //  売買居住用 完了画面
    const URI_KYOJUU_CONFIRM                = 31; //  売買居住用 確認画面
    const URI_KYOJUU_EDIT                   = 32; //  売買居住用 編集画面
    const CONTACT_ERROR                     = 33; //  お問い合わせエラー
    const HOWTOINFO                         = 34; //  情報の見方
    const SP_SELECT_PREFECTURE              = 35; //  特集：都道府県選択（物件種目トップ）
    const SP_SELECT_CITY                    = 36; //  特集：市区選択
    const SP_SELECT_RAILWAY                 = 37; //  特集：沿線選択
    const SP_SELECT_STATION                 = 38; //  特集：駅選択
    const SP_SELECT_STATION_MULTI_RAYWAY    = 39; //  特集：駅選択（複数路線選択時）
    const SP_RESULT_PREFECTURE              = 40; //  特集：都道府県物件一覧
    const SP_RESULT_AREA                    = 41; //  特集：エリアの物件一覧
    const SP_RESULT_AREA_MULTI              = 42; //  特集：エリアの物件一覧（複数）
    const SP_RESULT_MCITY                   = 43; //  特集：政令指定都市からの物件一覧
    const SP_RESULT_RAILWAY                 = 44; //  特集：沿線の物件一覧
    const SP_RESULT_STATION                 = 45; //  特集：駅からの物件一覧
    const SP_RESULT_STATION_MULTI           = 46; //  特集：沿線・駅の物件一覧（複数）
    const SP_RESULT_DIRECT_RESULT           = 47; //  特集：沿線・駅の物件一覧（複数）
    const MOBILE_SELECT_CONDITION           = 48; //  mobile 条件の絞込
    const MOBILE_RESULT_FROM_CONDITION      = 49; //  mobile こだわり条件からの物件一覧
    const MOBILE_RESULT_CHANGE_CONDITION    = 50; //  mobile 条件変更
    const MOBILE_SP_SELECT_CONDITION        = 51; //  mobile 特集：条件の絞込
    const MOBILE_SP_RESULT_FROM_CONDITION   = 52; //  mobile 特集：こだわり条件からの物件一覧
    const MOBILE_SP_RESULT_CHANGE_CONDITION = 53; //  mobile 特集：条件変更
    const API                               = 54; //  API
    const SP_API                            = 55; //  API
    const DETAIL_PANORAMA                   = 56; //  物件詳細のパノラマ
    const SELECT_MAP_CITY                   = 100; //  市区選択（地図検索）
    const RESULT_MAP                        = 101; //  地図表示
    const SP_SELECT_MAP_CITY                = 102; //  特集：市区選択（地図検索）
    const SP_RESULT_MAP                     = 103; //  特集：地図表示
    const API_MAP                           = 104; //  地図中心位置の取得
    const SP_API_MAP                        = 105; //  地図中心位置の取得
    //const MAP_GET_ESTATE                    = 105; //  地図表示物件の取得
    const CONTACT_VALIDATE                  = 106;//  お問い合わせ用バリデート

    const SELECT_CHOSON                    = 107; //  町名選択
    const SELECT_CHOSON_MULTI_CITY         = 108; //  町名選択（複数市区選択時）
    const SP_SELECT_CHOSON                 = 109; //  特集：町名選択
    const SP_SELECT_CHOSON_MULTI_CITY      = 110; //  特集：町名選択（複数市区選択時）
    const RESULT_CHOSON                    = 111; //  町名からの物件一覧
    const RESULT_CHOSON_MULTI              = 112; //  町名からの物件一覧（複数）
    const SP_RESULT_CHOSON                 = 113; //  特集：町名からの物件一覧
    const SP_RESULT_CHOSON_MULTI           = 114; //  特集：町名からの物件一覧（複数）
    const RESULT_FREEWORD                  = 115;
    const DETAIL_TOWN                      = 116;

    const API_ACCESSCOUNT                  = 120; // アクセスカウンター用




    const CATEGORY_SEARCH    = 1;
    const CATEGORY_PERSONAL  = 2;
    const CATEGORY_INQUIRY   = 3;
    const CATEGORY_SPECIAL   = 4;
    const CATEGORY_HOWTOINFO = 5;
    const CATEGORY_API       = 6;
    const CATEGORY_API_MAP   = 7;


    const FROM_PREFECTURE      = 'from_prefecture';
    const FROM_CITY_SELECT     = 'from_city_select';
    const FROM_STATION_SELECT  = 'from_station_select';
    const FROM_CHOSON_SELECT   = 'from_choson_select';
    const FROM_CONDITION       = 'from_condition';
    const FROM_RESULT          = 'from_result';
    const FROM_MAP_CITY_SELECT = 'from_map_city_select';
    const FROM_MAP_RESULT      = 'from_map_result';


    const S_TYPE_RESULT_RAILWAY       = 1;
    const S_TYPE_RESULT_CITY          = 2;
    const S_TYPE_RESULT_MCITY         = 3;
    const S_TYPE_RESULT_STATION       = 4;
    const S_TYPE_RESULT_MAP           = 5;
    const S_TYPE_RESULT_PREF          = 6;
    const S_TYPE_RESULT_CITY_FORM     = 7;
    const S_TYPE_RESULT_STATION_FORM  = 8;
    const S_TYPE_RESULT_DIRECT_RESULT = 9;
    const S_TYPE_RESULT_CHOSON        = 10;
    const S_TYPE_RESULT_CHOSON_FORM   = 11;
    const S_TYPE_RESULT_FREEWORD      = 12;

    //    static public function s_type_map() {
    //
    //        return [
    //            // search
    //            self::RESULT_RAILWAY          => self::S_TYPE_RESULT_RAILWAY,
    //            self::RESULT_AREA             => self::S_TYPE_RESULT_CITY,
    //            self::RESULT_MCITY            => self::S_TYPE_RESULT_MCITY,
    //            self::RESULT_STATION          => self::S_TYPE_RESULT_STATION,
    //            self::RESULT_PREFECTURE       => self::S_TYPE_RESULT_PREF,
    //            self::RESULT_AREA_MULTI       => self::S_TYPE_RESULT_CITY_FORM,
    //            self::RESULT_STATION_MULTI    => self::S_TYPE_RESULT_STATION_FORM,
    //            // special
    //            self::SP_RESULT_RAILWAY       => self::S_TYPE_RESULT_RAILWAY,
    //            self::SP_RESULT_AREA          => self::S_TYPE_RESULT_CITY,
    //            self::SP_RESULT_MCITY         => self::S_TYPE_RESULT_MCITY,
    //            self::SP_RESULT_STATION       => self::S_TYPE_RESULT_STATION,
    //            self::SP_RESULT_PREFECTURE    => self::S_TYPE_RESULT_PREF,
    //            self::SP_RESULT_AREA_MULTI    => self::S_TYPE_RESULT_CITY_FORM,
    //            self::SP_RESULT_STATION_MULTI => self::S_TYPE_RESULT_STATION_FORM,
    //        ];
    //    }
    //
    //    static public function get_s_type($key) {
    //
    //        return self::s_type_map()[$key];
    //    }

    static public function code_all() {

        return [
            self::SHUMOKU,
            self::RENT,
            self::PURCHASE,
            self::SELECT_PREFECTURE,
            self::SELECT_CITY,
            self::SELECT_RAILWAY,
            self::SELECT_STATION,
            self::SELECT_STATION_MULTI_RAYWAY,
            self::RESULT_RAILWAY,
            self::RESULT_AREA,
            self::RESULT_MCITY,
            self::RESULT_STATION,
            self::RESULT_PREFECTURE,
            self::RESULT_AREA_MULTI,
            self::RESULT_STATION_MULTI,
            self::DETAIL,
            self::DETAIL_MAP,
            self::DETAIL_PANORAMA,
            self::DETAIL_CLOSED,
            self::FAVORITE,
            self::HISTORY,
            self::KASI_JIGYOU_COMPLETE,
            self::KASI_JIGYOU_CONFIRM,
            self::KASI_JIGYOU_EDIT,
            self::KASI_KYOJUU_COMPLETE,
            self::KASI_KYOJUU_CONFIRM,
            self::KASI_KYOJUU_EDIT,
            self::URI_JIGYOU_COMPLETE,
            self::URI_JIGYOU_CONFIRM,
            self::URI_JIGYOU_EDIT,
            self::URI_KYOJUU_COMPLETE,
            self::URI_KYOJUU_CONFIRM,
            self::URI_KYOJUU_EDIT,
            self::CONTACT_ERROR,
            self::HOWTOINFO,
            self::SP_SELECT_PREFECTURE,
            self::SP_SELECT_CITY,
            self::SP_SELECT_RAILWAY,
            self::SP_SELECT_STATION,
            self::SP_SELECT_STATION_MULTI_RAYWAY,
            self::SP_RESULT_RAILWAY,
            self::SP_RESULT_AREA,
            self::SP_RESULT_MCITY,
            self::SP_RESULT_STATION,
            self::SP_RESULT_PREFECTURE,
            self::SP_RESULT_AREA_MULTI,
            self::SP_RESULT_STATION_MULTI,
            self::MOBILE_SELECT_CONDITION,
            self::MOBILE_SP_SELECT_CONDITION,
            self::MOBILE_RESULT_FROM_CONDITION,
            self::MOBILE_SP_RESULT_FROM_CONDITION,
            self::MOBILE_RESULT_CHANGE_CONDITION,
            self::MOBILE_SP_RESULT_CHANGE_CONDITION,
            self::API,
            self::SP_API,
            self::CONTACT_VALIDATE,

            self::SELECT_MAP_CITY,
            self::RESULT_MAP,
            self::SP_SELECT_MAP_CITY,
            self::SP_RESULT_MAP,

            self::SELECT_CHOSON,
            self::SELECT_CHOSON_MULTI_CITY,
            self::SP_SELECT_CHOSON,
            self::SP_SELECT_CHOSON_MULTI_CITY,
            self::RESULT_CHOSON,
            self::RESULT_CHOSON_MULTI,
            self::SP_RESULT_CHOSON,
            self::SP_RESULT_CHOSON_MULTI,
            self::RESULT_FREEWORD,
            self::DETAIL_TOWN,
        ];
    }

    static public function category_code_all() {

        return [
            self::CATEGORY_SEARCH,
            self::CATEGORY_PERSONAL,
            self::CATEGORY_INQUIRY,
            self::CATEGORY_SPECIAL,
            self::CATEGORY_HOWTOINFO,
            self::CATEGORY_API,
            self::CATEGORY_API_MAP,
        ];
    }

    static public function japanese_all() {

        return [
            self::SHUMOKU                           => '物件種目選択',
            self::RENT                              => '賃貸物件種目選択',
            self::PURCHASE                          => '売買物件種目選択',
            self::SELECT_PREFECTURE                 => '都道府県選択（物件種目トップ）',
            self::SELECT_CITY                       => '市区選択',
            self::SELECT_RAILWAY                    => '沿線選択',
            self::SELECT_STATION                    => '駅選択',
            self::SELECT_STATION_MULTI_RAYWAY       => '駅選択（複数路線選択時）',
            self::RESULT_RAILWAY                    => '沿線の物件一覧',
            self::RESULT_AREA                       => 'エリアの物件一覧',
            self::RESULT_MCITY                      => '政令指定都市からの物件一覧',
            self::RESULT_STATION                    => '駅からの物件一覧',
            self::RESULT_PREFECTURE                 => '都道府県物件一覧',
            self::RESULT_AREA_MULTI                 => 'エリアの物件一覧（複数）',
            self::RESULT_STATION_MULTI              => '沿線・駅の物件一覧（複数）',
            self::DETAIL                            => '物件詳細',
            self::DETAIL_MAP                        => '物件詳細の周辺環境',
            self::DETAIL_PANORAMA                   => '物件詳細のパノラマ',
            self::DETAIL_CLOSED                     => '物件詳細 公開終了',
            self::FAVORITE                          => 'お気に入り',
            self::HISTORY                           => '最近見た物件',
            self::KASI_JIGYOU_COMPLETE              => '賃貸事業用 完了画面',
            self::KASI_JIGYOU_CONFIRM               => '賃貸事業用 確認画面',
            self::KASI_JIGYOU_EDIT                  => '賃貸事業用 編集画面',
            self::KASI_KYOJUU_COMPLETE              => '賃貸居住用 完了画面',
            self::KASI_KYOJUU_CONFIRM               => '賃貸居住用 確認画面',
            self::KASI_KYOJUU_EDIT                  => '賃貸居住用 編集画面',
            self::URI_JIGYOU_COMPLETE               => '売買事業用 完了画面',
            self::URI_JIGYOU_CONFIRM                => '売買事業用 確認画面',
            self::URI_JIGYOU_EDIT                   => '売買事業用 編集画面',
            self::URI_KYOJUU_COMPLETE               => '売買居住用 完了画面',
            self::URI_KYOJUU_CONFIRM                => '売買居住用 確認画面',
            self::URI_KYOJUU_EDIT                   => '売買居住用 編集画面',
            self::CONTACT_ERROR                     => 'お問い合わせ エラー画面',
            self::HOWTOINFO                         => '情報の見方',
            self::SP_SELECT_PREFECTURE              => '特集：都道府県選択（物件種目トップ）',
            self::SP_SELECT_CITY                    => '特集：市区選択',
            self::SP_SELECT_RAILWAY                 => '特集：沿線選択',
            self::SP_SELECT_STATION                 => '特集：駅選択',
            self::SP_SELECT_STATION_MULTI_RAYWAY    => '特集：駅選択（複数路線選択時）',
            self::SP_RESULT_RAILWAY                 => '特集：沿線の物件一覧',
            self::SP_RESULT_AREA                    => '特集：エリアの物件一覧',
            self::SP_RESULT_MCITY                   => '特集：政令指定都市からの物件一覧',
            self::SP_RESULT_STATION                 => '特集：駅からの物件一覧',
            self::SP_RESULT_PREFECTURE              => '特集：都道府県物件一覧',
            self::SP_RESULT_AREA_MULTI              => '特集：エリアの物件一覧（複数）',
            self::SP_RESULT_STATION_MULTI           => '特集：沿線・駅の物件一覧（複数）',
            self::MOBILE_SELECT_CONDITION           => '条件の絞込（モバイル）',
            self::MOBILE_SP_SELECT_CONDITION        => '特集：条件の絞込（モバイル）',
            self::MOBILE_RESULT_FROM_CONDITION      => 'こだわり条件からの物件一覧（モバイル）',
            self::MOBILE_SP_RESULT_FROM_CONDITION   => '特集：こだわり条件からの物件一覧（モバイル）',
            self::MOBILE_RESULT_CHANGE_CONDITION    => '条件変更（モバイル）',
            self::MOBILE_SP_RESULT_CHANGE_CONDITION => '特集：条件変更（モバイル）',
            self::API                               => 'API',
            self::SP_API                            => '特集：API',

            self::SELECT_MAP_CITY                   => '市区選択（地図検索）',
            self::RESULT_MAP                        => '地図表示',
            self::SP_SELECT_MAP_CITY                => '特集：市区選択（地図検索）',
            self::SP_RESULT_MAP                     => '特集：地図表示',

            self::SELECT_CHOSON                    => '町名選択',
            self::SELECT_CHOSON_MULTI_CITY         => '町名選択（複数市区選択時）',
            self::SP_SELECT_CHOSON                 => '特集：町名選択',
            self::SP_SELECT_CHOSON_MULTI_CITY      => '特集：町名選択（複数市区選択時）',
            self::RESULT_CHOSON                    => '町名からの物件一覧',
            self::RESULT_CHOSON_MULTI              => '町名からの物件一覧（複数）',
            self::SP_RESULT_CHOSON                 => '特集：町名からの物件一覧',
            self::SP_RESULT_CHOSON_MULTI           => '特集：町名からの物件一覧（複数）',
            self::RESULT_FREEWORD                  => 'フリーワード検索',
            self::DETAIL_TOWN                      => ''
        ];
    }

    static public function filename_all() {

        return [
            self::SHUMOKU                           => 'shumoku.php',
            self::RENT                              => 'rent.php',
            self::PURCHASE                          => 'purchase.php',
            self::SELECT_PREFECTURE                 => 'select_prefecture.php',
            self::SELECT_CITY                       => 'select_city.php',
            self::SELECT_RAILWAY                    => 'select_railway.php',
            self::SELECT_STATION                    => 'select_station.php',
            self::SELECT_STATION_MULTI_RAYWAY       => 'select_station_from_multi_railway.php',
            self::RESULT_RAILWAY                    => 'result_railway.php',
            self::RESULT_AREA                       => 'result_area.php',
            self::RESULT_MCITY                      => 'result_mcity.php',
            self::RESULT_STATION                    => 'result_station.php',
            self::RESULT_PREFECTURE                 => 'result_prefecture.php',
            self::RESULT_AREA_MULTI                 => 'result_area_form.php',
            self::RESULT_STATION_MULTI              => 'result_train_form.php',
            self::DETAIL                            => 'detail.php',
            self::DETAIL_MAP                        => 'detail_map.php',
            self::DETAIL_PANORAMA                   => 'detail_panorama.php',
            self::DETAIL_CLOSED                     => 'detail_closed.php',
            self::FAVORITE                          => 'favorite.php',
            self::HISTORY                           => 'history.php',
            self::KASI_JIGYOU_COMPLETE              => 'kasi_jigyou_complete.php',
            self::KASI_JIGYOU_CONFIRM               => 'kasi_jigyou_confirm.php',
            self::KASI_JIGYOU_EDIT                  => 'kasi_jigyou_edit.php',
            self::KASI_KYOJUU_COMPLETE              => 'kasi_kyojuu_complete.php',
            self::KASI_KYOJUU_CONFIRM               => 'kasi_kyojuu_confirm.php',
            self::KASI_KYOJUU_EDIT                  => 'kasi_kyojuu_edit.php',
            self::URI_JIGYOU_COMPLETE               => 'uri_jigyou_complete.php',
            self::URI_JIGYOU_CONFIRM                => 'uri_jigyou_confirm.php',
            self::URI_JIGYOU_EDIT                   => 'uri_jigyou_edit.php',
            self::URI_KYOJUU_COMPLETE               => 'uri_kyojuu_complete.php',
            self::URI_KYOJUU_CONFIRM                => 'uri_kyojuu_confirm.php',
            self::URI_KYOJUU_EDIT                   => 'uri_kyojuu_edit.php',
            self::CONTACT_ERROR                     => 'contact_error.php',
            self::HOWTOINFO                         => 'howtoinfo.php',
            self::SP_SELECT_PREFECTURE              => 'sp_select_prefecture.php',
            self::SP_SELECT_CITY                    => 'sp_select_city.php',
            self::SP_SELECT_RAILWAY                 => 'sp_select_railway.php',
            self::SP_SELECT_STATION                 => 'sp_select_station.php',
            self::SP_SELECT_STATION_MULTI_RAYWAY    => 'sp_select_station_from_multi_railway.php',
            self::SP_RESULT_RAILWAY                 => 'sp_result_railway.php',
            self::SP_RESULT_AREA                    => 'sp_result_area.php',
            self::SP_RESULT_MCITY                   => 'sp_result_mcity.php',
            self::SP_RESULT_STATION                 => 'sp_result_station.php',
            self::SP_RESULT_PREFECTURE              => 'sp_result_prefecture.php',
            self::SP_RESULT_AREA_MULTI              => 'sp_result_area_form.php',
            self::SP_RESULT_STATION_MULTI           => 'sp_result_train_form.php',
            self::SP_RESULT_DIRECT_RESULT           => 'sp_result_direct_result.php',
            self::MOBILE_SELECT_CONDITION           => 'select_condition.php',
            self::MOBILE_SP_SELECT_CONDITION        => 'sp_select_condition.php',
            self::MOBILE_RESULT_FROM_CONDITION      => 'result_condition.php',
            self::MOBILE_SP_RESULT_FROM_CONDITION   => 'sp_result_condition.php',
            self::MOBILE_RESULT_CHANGE_CONDITION    => 'result_change_condition.php',
            self::MOBILE_SP_RESULT_CHANGE_CONDITION => 'sp_result_change_condition.php',
            self::API                               => 'api.php',
            self::SP_API                            => 'sp_api.php',
            self::CONTACT_VALIDATE                  => 'contact_validate.php',

            self::SELECT_MAP_CITY                   => 'select_map_city.php',
            self::RESULT_MAP                        => 'result_map.php',
            self::SP_SELECT_MAP_CITY                => 'sp_select_map_city.php',
            self::SP_RESULT_MAP                     => 'sp_result_map.php',

            self::SELECT_CHOSON                    => 'select_choson.php',
            self::SELECT_CHOSON_MULTI_CITY         => 'select_choson_from_multi_city.php',
            self::SP_SELECT_CHOSON                 => 'sp_select_choson.php',
            self::SP_SELECT_CHOSON_MULTI_CITY      => 'sp_select_choson_from_multi_city.php',
            self::RESULT_CHOSON                    => 'result_choson.php',
            self::RESULT_CHOSON_MULTI              => 'result_choson_form.php',
            self::SP_RESULT_CHOSON                 => 'sp_result_choson.php',
            self::SP_RESULT_CHOSON_MULTI           => 'sp_result_choson_form.php',
            self::RESULT_FREEWORD                  => 'result_freeword.php',
            self::DETAIL_TOWN                      => 'detail_town.php',
        ];
    }

    static public function category_map() {

        return [
            self::CATEGORY_SEARCH    => [
                self::SHUMOKU,
                self::RENT,
                self::PURCHASE,
                self::SELECT_PREFECTURE,
                self::SELECT_CITY,
                self::SELECT_RAILWAY,
                self::SELECT_STATION,
                self::SELECT_STATION_MULTI_RAYWAY,
                self::RESULT_RAILWAY,
                self::RESULT_AREA,
                self::RESULT_MCITY,
                self::RESULT_STATION,
                self::RESULT_PREFECTURE,
                self::RESULT_AREA_MULTI,
                self::RESULT_STATION_MULTI,
                self::DETAIL,
                self::DETAIL_MAP,
                self::DETAIL_PANORAMA,
                self::DETAIL_CLOSED,
                self::MOBILE_SELECT_CONDITION,
                self::MOBILE_RESULT_FROM_CONDITION,
                self::SELECT_MAP_CITY,
                self::RESULT_MAP,

                self::SELECT_CHOSON,
                self::SELECT_CHOSON_MULTI_CITY,
                self::RESULT_CHOSON,
                self::RESULT_CHOSON_MULTI,
                self::RESULT_FREEWORD,
            ],
            self::CATEGORY_PERSONAL  => [
                self::FAVORITE,
                self::HISTORY,
            ],
            self::CATEGORY_INQUIRY   => [
                self::KASI_JIGYOU_COMPLETE,
                self::KASI_JIGYOU_CONFIRM,
                self::KASI_JIGYOU_EDIT,
                self::KASI_KYOJUU_COMPLETE,
                self::KASI_KYOJUU_CONFIRM,
                self::KASI_KYOJUU_EDIT,
                self::URI_JIGYOU_COMPLETE,
                self::URI_JIGYOU_CONFIRM,
                self::URI_JIGYOU_EDIT,
                self::URI_KYOJUU_COMPLETE,
                self::URI_KYOJUU_CONFIRM,
                self::URI_KYOJUU_EDIT,
                self::CONTACT_ERROR,
                self::CONTACT_VALIDATE,
            ],
            self::CATEGORY_SPECIAL   => [
                self::SP_SELECT_PREFECTURE,
                self::SP_SELECT_CITY,
                self::SP_SELECT_RAILWAY,
                self::SP_SELECT_STATION,
                self::SP_SELECT_STATION_MULTI_RAYWAY,
                self::SP_RESULT_RAILWAY,
                self::SP_RESULT_AREA,
                self::SP_RESULT_MCITY,
                self::SP_RESULT_STATION,
                self::SP_RESULT_PREFECTURE,
                self::SP_RESULT_AREA_MULTI,
                self::SP_RESULT_STATION_MULTI,
                self::MOBILE_SP_SELECT_CONDITION,
                self::MOBILE_SP_RESULT_FROM_CONDITION,
                self::MOBILE_SP_RESULT_CHANGE_CONDITION,
                self::SP_SELECT_MAP_CITY,
                self::SP_RESULT_MAP,

                self::SP_SELECT_CHOSON,
                self::SP_SELECT_CHOSON_MULTI_CITY,
                self::SP_RESULT_CHOSON,
                self::SP_RESULT_CHOSON_MULTI,
            ],
            self::CATEGORY_HOWTOINFO => [
                self::HOWTOINFO,
            ],
            self::CATEGORY_API       => [
                self::API,
                self::SP_API,
                self::API_ACCESSCOUNT,
            ],
            self::CATEGORY_API_MAP   => [
                self::API_MAP,
                self::SP_API_MAP,
            ],
        ];
    }

    static public function category_by_code($code) {

        foreach (self::category_map() as $key => $array) {

            if (in_array($code, $array)) {
                return $key;
            }
        };
        return null;
    }

    static public function japanese_by_code($code) {

        return self::japanese_all()[$code];
    }

    static public function filename_by_code($page_code) {

        return self::filename_all()[$page_code];
    }

    static public function post_only_pages() {

        return [
            self::SELECT_STATION_MULTI_RAYWAY,
            self::RESULT_AREA_MULTI,
            self::RESULT_STATION_MULTI,
            self::SP_SELECT_STATION_MULTI_RAYWAY,
            self::SP_RESULT_AREA_MULTI,
            self::SP_RESULT_STATION_MULTI,
            self::MOBILE_SELECT_CONDITION,
            self::MOBILE_RESULT_FROM_CONDITION,
            self::MOBILE_RESULT_CHANGE_CONDITION,
            self::MOBILE_SP_SELECT_CONDITION,
            self::MOBILE_SP_RESULT_FROM_CONDITION,
            self::MOBILE_SP_RESULT_CHANGE_CONDITION,
            self::SELECT_CHOSON_MULTI_CITY,
            self::SP_SELECT_CHOSON_MULTI_CITY,
            self::RESULT_CHOSON_MULTI,
            self::SP_RESULT_CHOSON_MULTI,
        ];
    }

    static public function post_only($page_code) {

        return in_array($page_code, self::post_only_pages());
    }

    static public function protocol($page_code) {

        return empty($_SERVER['HTTPS']) ? 'http' : 'https';
        // return in_array($page_code, self::category_map()[self::CATEGORY_INQUIRY]) ? 'https' : 'http';
    }

    static public function redirect_path($page_code, Request $request) {

        switch ($page_code) {
            // 市区選択
            case self::SELECT_CHOSON_MULTI_CITY:
            case self::SP_SELECT_CHOSON_MULTI_CITY:
                return '/'.$request->directory(1).'/'.$request->directory(2).'/';
            // 沿線選択
            case self::SELECT_STATION_MULTI_RAYWAY:
            case self::SP_SELECT_STATION_MULTI_RAYWAY:
                return '/'.$request->directory(1).'/'.$request->directory(2).'/'.'line.html';
            // お問い合わせ
            case self::KASI_JIGYOU_CONFIRM:
            case self::KASI_KYOJUU_CONFIRM:
            case self::URI_JIGYOU_CONFIRM:
            case self::URI_KYOJUU_CONFIRM:
                return '/'.$request->directory(1).'/'.$request->directory(2).'/'.'error'.'/';
        }

        return '/';
    }

}