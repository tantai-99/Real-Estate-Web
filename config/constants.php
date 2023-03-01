<?php
return [
    'Manager' =>
    [
        'LOGIN_FAILED_LIMIT' => 10,
    ],

    'company_agreement_type' =>
    [
        /**
         * 契約タイプ 本契約
         */
        'CONTRACT_TYPE_PRIME' => 0,

        /**
         * 契約タイプ デモ
         */
        'CONTRACT_TYPE_DEMO' => 1,

        /**
         * 契約タイプ 評価・分析のみ
         */
        'CONTRACT_TYPE_ANALYZE' => 2,
    ],

    'cms_plan' =>
    [
        'ORIGINAL_ENABLE' => 1,

        /**
         * アドバンスプラン
         */
        'CMS_PLAN_ADVANCE'        => 70,

        /**
         * スタンダードプラン
         */
        'CMS_PLAN_STANDARD'        => 40,

        /**
         * ライトプラン Plan lite
         */
        'CMS_PLAN_LITE'         => 10,

        /**
         * 現在の最上位プラン
         */
        'TOP_PLAN'            => 70,

        /**
         * 未選択
         */
        'CMS_PLAN_NONE'            =>  0,
    ],

    'original' =>
    [
        'MAX_TYPE_INFO_INDEX' => 2,
        'MAX_GLOBAL_NAVIGATION' => 9,
        'DEFAULT_GLOBAL_NAVIGATION' => 6,
        'MAX_NOTIFICATION_PAGE_SIZE' => 30,
        'DEFAULT_NOTIFICATION_PAGE_SIZE' => 1,
        'DEFAULT_NOTIFICATION_CMS_DISABLE' => 0,
        'KOMA_COLUMN' => 30,
        'KOMA_ROW' => 10,

        'DEFAULT_THEME' => 1,
        'DEFAULT_LAYOUT' => 1,
        'DEFAULT_COLOR' => 1,

        'TOP_CONTENT' => 'トップページ/サイドコンテンツ',
        'TITLE_DISABLE' => "現在のご契約プランではこのページの「ページタイトル」は変更できません",
        'PAGE_NAME_DISABLE' => "現在のご契約プランではこのページの「ページ名」は変更できません",
        'CONTRACT_TITLE' => "契約情報",

        'NEWS_INDEX_ID' => 'attr_1',

        'CURRENT_DATE' => 0,
        'PAST_DATE' => 1,
        'FUTURE_DATE' => 2,

        'ORIGINAL_SETTING_TITLE' => 1,
        'ORIGINAL_SETTING_SUB_TITLE' => 2,
        'ORIGINAL_EDIT_TITLE' => 3,
        'ORIGINAL_EDIT_SUB_TITLE' => 4,
        'ORIGINAL_EDIT_CMS' => 5,

        'ORIGINAL_EDIT_NAVIGATION' => 6,
        'ORIGINAL_EDIT_SPECIAL' => 7,
        'ORIGINAL_EDIT_NOTIFICATION' => 8,
        'ORIGINAL_EDIT_FILE' => 9,
        'ORIGINAL_EDIT_AGENCY' => 10,

        'ORIGINAL_IMPORT_TOPROOT' => 'html',
        'ORIGINAL_IMPORT_TOPKOMA' => 'bukken_koma',
        'ORIGINAL_IMPORT_TOPCSS' => 'top_css',
        'ORIGINAL_IMPORT_TOPJS' => 'top_js',
        'ORIGINAL_IMPORT_TOPIMAGE' => 'top_images',

        'companyTopEvent' => 'companyTopEvent',
    ],

    'ftp_pasv_mode' =>
    [
        /**
         * 有効
         */
        'IN_FORCE' => 0,

        /**
         * 無効
         */
        'INVALID' => 1,
    ],

    'hp_page' =>
    [
        'MAX_LEVEL' => 4,

        // トップページ
        'TYPE_TOP' => 1,

        // お知らせ一覧
        'TYPE_INFO_INDEX' => 2,

        // お知らせ
        'TYPE_INFO_DETAIL' => 3,

        // 会社紹介
        'TYPE_COMPANY' => 4,

        // 会社沿革
        'TYPE_HISTORY' => 5,

        // 代表挨拶
        'TYPE_GREETING' => 6,

        // 店舗紹介一覧
        'TYPE_SHOP_INDEX' => 7,

        // 店舗紹介
        'TYPE_SHOP_DETAIL' => 8,

        // スタッフ紹介一覧
        'TYPE_STAFF_INDEX' => 9,

        // スタッフ紹介
        'TYPE_STAFF_DETAIL' => 10,

        // 採用情報
        'TYPE_RECRUIT' => 11,

        // 物件ページ(物件コマ)一覧
        'TYPE_STRUCTURE_INDEX' => 12,

        // 物件ページ(物件コマ)
        'TYPE_STRUCTURE_DETAIL' => 13,

        // ブログ一覧
        'TYPE_BLOG_INDEX' => 14,

        // ブログ詳細
        'TYPE_BLOG_DETAIL' => 15,

        //CMSテンプレートパターンの追加
        // コラム一覧
        'TYPE_COLUMN_INDEX' => 56,
        // コラム詳細
        'TYPE_COLUMN_DETAIL' => 57,
        //CMSテンプレートパターンの追加

        // プライバシーポリシー
        'TYPE_PRIVACYPOLICY' => 16,

        // サイトポリシー
        'TYPE_SITEPOLICY' => 17,

        // オーナーページ
        'TYPE_OWNER' => 18,

        // 法人ページ
        'TYPE_CORPORATION' => 19,

        // 入居者さま向けページ
        'TYPE_TENANT' => 20,

        // 仲介会社さま向けページ
        'TYPE_BROKER' => 21,

        // 管理会社さま向けページ
        'TYPE_PROPRIETARY' => 22,

        // 街情報
        'TYPE_CITY' => 23,

        // お客様の声一覧
        'TYPE_CUSTOMERVOICE_INDEX' => 24,

        // お客様の声
        'TYPE_CUSTOMERVOICE_DETAIL' => 25,

        // Q＆Aページ
        'TYPE_QA' => 26,

        // カテゴリ リンク
        'CATEGORY_LINK' => 15,

         // 不動産お役立ち情報
        'TYPE_USEFUL_REAL_ESTATE_INFORMATION' => 100,

        // オリジナル小カテゴリー
        'TYPE_SMALL_ORIGINAL' => 342,

        // 物件ページエイリアス
        'TYPE_ESTATE_ALIAS' => 92,

        // サイトマップ
        'TYPE_SITEMAP' => 49,

        // カテゴリ サイトマップ
        'CATEGORY_SITEMAP' => 13,

        // オリジナル記事
        'TYPE_ARTICLE_ORIGINAL' => 343,

        // 物件問い合わせ 居住用賃貸物件フォーム
        'TYPE_FORM_LIVINGLEASE' => 44,

        // 物件問い合わせ 事務所用賃貸物件フォーム
        'TYPE_FORM_OFFICELEASE' => 45,

        // 物件問い合わせ 居住用売買物件フォーム
        'TYPE_FORM_LIVINGBUY' => 46,

        // 物件問い合わせ 事務所用売買物件フォーム
        'TYPE_FORM_OFFICEBUY' => 47,

        // 物件リクエスト
        // 物件リクエスト 居住用賃貸物件フォーム
        'TYPE_FORM_REQUEST_LIVINGLEASE' => 50,

        // 物件リクエスト 事務所用賃貸物件フォーム
        'TYPE_FORM_REQUEST_OFFICELEASE' => 51,

        // 物件リクエスト 居住用売買物件フォーム
        'TYPE_FORM_REQUEST_LIVINGBUY' => 52,

        // 物件リクエスト 事務所用売買物件フォーム
        'TYPE_FORM_REQUEST_OFFICEBUY' => 53,

         // 会社問い合わせ
        'TYPE_FORM_CONTACT' => 41,

        // 資料請求
        'TYPE_FORM_DOCUMENT' => 42,

        // 査定依頼
        'TYPE_FORM_ASSESSMENT' => 43,

        'CATEGORY_TOP_ARTICLE' => 22,

        'TYPE_LARGE_ORIGINAL' => 341,

        'CATEGORY_LARGE' => 23,

        'CATEGORY_SMALL' => 24,

        'CATEGORY_ARTICLE' => 25,
    ],

    'hp_estate_setting' => [
        'SETTING_FOR_CMS'    => 1,
        'SETTING_FOR_TEST'   => 2,
        'SETTING_FOR_PUBLIC' => 3,
        'row' => [
            'ESTATE_LINK_TYPE' => 'top',
            'ESTATE_RENT_LINK_TYPE' => 'rent',
            'ESTATE_PURCHASE_LINK_TYPE' => 'purchase',
        ]
    ],

    'tag_repository' => [
        'ALL_TAGS_COL' => 'all_tags',
    ],

    'manager_account_authority' => [
        //修正権限
        'PRIVILEGE_EDIT' => 1,

        //管理権限
        'PRIVILEGE_MANAGE' => 2,

        //代行作成権限
        'PRIVILEGE_CREATE' => 3,

        //代行更新権限
        'PRIVILEGE_OPEN' => 4,
    ],

    'new_mark' => [
        'COMMON' => 14,
        'NOT_USED' => 0,
        'NEW_MARK' => '<span class="new-mark">NEW</span>',
    ],

    'estate_koma' => [
        'SPECIAL_ID_ATTR' => 'attr_1',
    ],

    'information_display_page_code' => [
        /**
         * 非公開
         */
        'PRIVATE_VIEW' => 1,

        /**
         * ログイン前表示
         */
        'LOGIN_BEFORE_VIEW' => 2,

        /**
         * ログイン後表示
         */
        'AFTER_LOGGING_VIEW' => 3,

        /**
         * 全て表示
         */
        'ALL_VIEW' => 4,
    ],
    'information_display_type_code' => [
        /**
         * 指定ＵＲＬ
         */
        'URL' => 1,

        /**
         * 詳細ページ
         */
        'DETAIL_PAGE' => 2,

        /**
         * ファイルリンク
         */
        'FILE_LINK' => 3,
        
    ],
    'spamblock' => [
        'ALL_MEMBER' => 0,
        'SPECIFIC_MEMBER' => 1,
        'PERFECT_MATCH' => 0,
        'PARTIAL_MATCH' => 1,
    ],
    'log_type' => [
        'LOGIN'    => 1,
        'CREATE'   => 2,
        'COMPANY'  => 3,
        'PUBLISH'  => 4,
    ],
    'special_estate' => [
        'ORDER_CREATED_DESC' => 1,
        'ORDER_CREATED_ASC'  => 2,
        'ORDER_TITLE_ASC'    => 3,
        'ORDER_TITLE_DESC'   => 4,
        'ORDER_PUB_STATUS'   => 5,
        'ORDER_ESTATE_CLASS' => 6,
        'ORDER_CREATED_DESC_ID_DESC' => 7,
        'row' => [
            'ESTATE_LINK_TYPE' => 'special',
            'PUBLISH_STATUS_NEW'     => 'new', // 新規
            'PUBLISH_STATUS_UPDATE'  => 'update', // 修正
            'PUBLISH_STATUS_NO_DIFF' => 'no_diff', // 差分なし
        ]
    ],
    'release_schedule' => [
        'RESERVE_RELEASE'     => 1, // 公開予約
        'RESERVE_CLOSE'       => 2, // 下書き予約
        'RESERVE_RELEASE_PRE' => 3, // 公開一時予約
        'RESERVE_CLOSE_PRE'   => 4 // 下書き一時予約
    ],

    'hp' => [
        // 未着手
        'INITIAL_SETTING_STATUS_NEW' => 0,

        // 初期設定まで完了
        'INITIAL_SETTING_STATUS_INIT' => 1,

        // デザイン選択まで完了
        'INITIAL_SETTING_STATUS_DESIGN' => 2,

        // トップページ作成まで完了
        'INITIAL_SETTING_STATUS_TOPPAGE' => 3,

        // 会社紹介ページ作成まで完了
        'INITIAL_SETTING_STATUS_COMPANYPROFILE' => 4,

        // プライバシーポリリー作成まで完了
        'INITIAL_SETTING_STATUS_PRIVACYPOLICY' => 5,

        // サイトポリシー作成まで完了
        'INITIAL_SETTING_STATUS_SITEPOLICY' => 6,

        // 会社問い合わせ作成まで完了
        'INITIAL_SETTING_STATUS_CONTACT' => 7,

        // 完了
        'INITIAL_SETTING_STATUS_COMPLETE' => 99,

        // Twitter widget-id
        // @todo 正しいモノに更新する
        'DEFAULT_TWITTER_WIDGET_ID' => '560251342191464448',

        //アドバンスのＣＭＳにて下記の容量が超えたら画像とかは登録出来なくする（単位はＭＢ）
        'SITE_OBER_CAPASITY_DATAMAX' => 5120,

        'SIDELAYOUT_ESTATE_RENT' => 1,
        'SIDELAYOUT_ESTATE_PURCHASE' => 2,
        'SIDELAYOUT_OTHER_LINK' => 3,
        'SIDELAYOUT_CUSTOMIZED_CONTENTS' => 4,
        'SIDELAYOUT_ARTICLE_LINK' => 5,

        'SIDELAYOUT_OTHER_LINK_TITLE' => 'コンテンツ一覧',
        'SIDELAYOUT_ARTICLE_LINK_TITLE' => '不動産お役立ち情報',
    ],
    'hp_site_image' => [
        'TYPE_FAVICON' => 1,

        'TYPE_SITELOGO_PC' => 2,

        'TYPE_SITELOGO_SP' => 3,

        'TYPE_WEBCLIP' => 4,
    ],

    'site_logo_type' => [
        'IMAGE' => 1,
        'IMAGE_TEXT' => 2,
    ],

    'qr_type' => [
        'COMMON' => 1,
        'INDIVIDUAL' => 2,
    ],

    'footer_link_level' => [
        'COMMON' => 5,
        'INDIVIDUAL' => 2,
    ],
    'log_edit_type' => [
        'LOGIN'                 => 1,

        'PAGE_CREATE'           => 2,
        'PAGE_UPDATE'           => 3,
        'PAGE_DELETE'           => 4,

        'SITESETTING_UPDATE'    => 5,
        'DESIGN_UPDATE'         => 6,

        'IMAGE_CREATE'          => 7,
        'PUBLISH_TEST'          => 8,
        'PUBLISH'               => 9,

        // 代行作成ログ -->>
        'CREATOR_DATA_COPY'     => 10,
        'CREATOR_DATA_DELETE'   => 11,
        'CREATOR_UPDATE'        => 12,
        'CREATOR_TEST'          => 13,
        'CREATOR_ROLLBACK'      => 14,
        // 代行作成ログ <<--

        'CREATE_FILE2'          => 15,


        //物件設定
        'ESTATE_SETTING_CREATE'    => 20,
        'ESTATE_SETTING_UPDATE'    => 21,
        'ESTATE_SETTING_DELETE'    => 22,

        //特集設定
        'SPECIAL_SETTING_CREATE'   => 30,
        'SPECIAL_SETTING_UPDATE'   => 31,
        'SPECIAL_SETTING_DELETE'   => 32,
        'SPECIAL_SETTING_COPY'     => 33,

        //2次広告自動公開
        'SECOND_SETTING_CREATE'    => 40,
        'SECOND_SETTING_UPDATE'    => 41,
        //2次広告自動公開の除外設定
        'SECOND_SETTING_EXCLUSION_UPDATE'    => 45,
        'SECOND_SETTING_EXCLUSION_DELETE'    => 46,
    ],
    'hp_image_category' => [
        'ADVANCE_CATEGORY'      => 10000 ,
        'STANDARD_CATEGORY'     => 10000 ,
        'LITE_CATEGORY'         => 10000 ,
    ],
    'publish_type' => [
        'TYPE_PUBLIC' => 1,
        'TYPE_TESTSITE' => 2,
        'TYPE_SUBSTITUTE' => 3,
        'TYPE_PREVIEW' => 4
    ],
    'link_type' => [
        'PAGE' => 1,
        'URL'  => 2,
        'FILE' => 3,
        'HOUSE' => 4,
    ],
    'api_gateway' => [

        'KEY_COM_ID'            => 'com_id',
        'KEY_API_KEY'           => 'api_key',
        'KEY_CMS_SPECIAL'       => 'cms_special',
        'KEY_PUBLISH'           => 'publish',
        'KEY_SHUMOKU'           => 'type_ct',
        'KEY_PER_PAGE'          => 'per_page',
        'KEY_SORT'              => 'sort_cms',
        'KEY_PAGE'              => 'page',
        'KEY_BUKKEN_NO'         => 'bukken_no',
        'KEY_BUKKEN_ID'         => 'bukken_id',
        'KEY_SETTING'           => 'setting',
        'KEY_IS_COUNT'          => 'is_count',
        'KEY_IS_MODAL'          => 'is_modal',
        'KEY_IS_CONFIRM'        => 'is_confirm',
        'KEY_IS_CONDITION'      => 'is_condition',
        'KEY_LINK_PAGE'         => 'link_page',
        'KEY_IS_TITLE'          => 'is_title',
    ]

];
