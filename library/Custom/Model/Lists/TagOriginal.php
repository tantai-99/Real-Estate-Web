<?php
namespace Library\Custom\Model\Lists;

class TagOriginal extends ListAbstract {

    	static protected $_instance;

    	const GLONAVI_URL = 'glonavi_url';
        const GLONAVI_LABEL = 'glonavi_label';

        // category tag
        const CATEGORY_TAG_SITE             = 'category.tag.site';
        const CATEGORY_TAG_NEWS             = 'category.tag.news';
        const CATEGORY_TAG_PROPERTY         = 'category.tag.property';
        const CATEGORY_TAG_COMPONENT        = 'category.tag.component';
        const CATEGORY_TAG_ELEMENT          = 'category.tag.element';

        // NHP-3751:フリーワード検索
        const CATEGORY_TAG_FREEWORD_PARTS   = 'category.tag.freeword_parts';

        const TAG_ELEMENT_NEWS1         = 'tag.element.news1';
        const TAG_ELEMENT_NEWS2         = 'tag.element.news2';
        const TAG_ELEMENT_CHINTAI       = 'tag.element.chintai';
        const TAG_ELEMENT_BUY           = 'tag.element.buy';
        const TAG_ELEMENT_SIDELINK      = 'tag.element.sidelink';
        const TAG_ELEMENT_CUSTOMIZE     = 'tag.element.customize';
        const TAG_ELEMENT_ARTICLELINK   = 'tag.element.articlelink';
        const TAG_ELEMENT_GOOGLE_MAP_API_KEY = 'tag.element.google_map_api_key';

        const TAG_GOOGLE_MAP            = 'tag.google_map';
        const TAG_TWITTER               = 'tag.twitter';
        const TAG_FACEBOOK              = 'tag.facebook';
        const TAG_LINE                  = 'tag.line';
        const TAG_SITE_URL              = 'tag.site.url';
        const TAG_SITEMAP               = 'tag.site.map';
        const TAG_SITE_NAME             = 'tag.site.name';
        const TAG_SITE_DESCRIPTION      = 'tag.site.description';
        const TAG_SITE_KEYWORD          = 'tag.site.keyword';
        const TAG_PAGE_TITLE            = 'tag.page.title';
        const TAG_PAGE_DESCRIPTION      = 'tag.page.description';
        const TAG_PAGE_KEYWORD          = 'tag.page.keyword';
        // const TAG_PAGE_NAME             = 'tag.page.name';
        
        const TAG_COMPONENT_HEADABOVE    = 'tag.component.head_above';
        const TAG_COMPONENT_BODYUNDER    = 'tag.component.body_under';
        const TAG_COMPONENT_BODYABOVE    = 'tag.component.body_above';

        const TAG_NEWS_DETAIL           = 'tag.news.detail';
        const TAG_NEWS_LIST1             = 'tag.news.list1';
        const TAG_NEWS_LIST2             = 'tag.news.list2';
        const TAG_NEWS_TITLE            = 'tag.news.title';
        const TAG_NEWS_DATE             = 'tag.news.date';
        const TAG_NEWS_YEAR             = 'tag.news.year';
        const TAG_NEWS_MONTH            = 'tag.news.month';
        const TAG_NEWS_DAILY            = 'tag.news.daily';
        // const TAG_NEWS_TIME             = 'tag.news.time';
        // const TAG_NEWS_MINUTE           = 'tag.news.minute';
        const TAG_NEWS_WEEK1            = 'tag.news.week1';
        const TAG_NEWS_WEEK2            = 'tag.news.week2';
        const TAG_NEWS_WEEK3            = 'tag.news.week3';
        const TAG_NEWS_WEEK4            = 'tag.news.week4';
        const TAG_NEWS_TEXT             = 'tag.news.text';
        const TAG_NEWS_CATEGORY         = 'tag.news.category';
        const TAG_NEWS_CATEGORY_CLASS   = 'tag.news.category_class';
        const TAG_NEWS_NEW_MARK         = 'tag.news.new_mark';

        // NHP-3751:フリーワード検索をオリジナルタグとしてHTMLに組み込めるようにする
        const TAG_FREEWORD_FORM         = 'tag.freeword.form';
        const TAG_FREEWORD_TYPESEL      = 'tag.freeword.type';
        const TAG_FREEWORD_TYPE_RESIDENTIAL_CHINTAI = 'tag.freeword.type_residential_chintai';
        const TAG_FREEWORD_TYPE_BUSINESS_CHINTAI    = 'tag.freeword.type_business_chintai';
        const TAG_FREEWORD_TYPE_RESIDENTIAL_BUY     = 'tag.freeword.type_residential_buy';
        const TAG_FREEWORD_TYPE_BUSINESS_BUY        = 'tag.freeword.type_business_buy';
        const TAG_FREEWORD_TEXT         = 'tag.freeword.text';
        const TAG_FREEWORD_COUNTER      = 'tag.freeword.counter';
        const TAG_FREEWORD_BUTTON       = 'tag.freeword.button';

        const TAG_PROPERTY_TYPE                 = 'tag.property.type';
        const TAG_PROPERTY_IMAGE1               = 'tag.property.image1';
        const TAG_PROPERTY_IMAGE2               = 'tag.property.image2';
        const TAG_PROPERTY_IMAGE3               = 'tag.property.image3';
        const TAG_PROPERTY_IMAGE4               = 'tag.property.image4';
        const TAG_PROPERTY_TRAFFIC              = 'tag.property.traffic';
        const TAG_PROPERTY_LOCATION             = 'tag.property.location';
        const TAG_PROPERTY_STATIONWALKING       = 'tag.property.station_walking';
        const TAG_PROPERTY_RENT                 = 'tag.property.rent';
        const TAG_PROPERTY_PRICE1               = 'tag.property.price1';
        const TAG_PROPERTY_PRICE2               = 'tag.property.price2';
        const TAG_PROPERTY_PRICE3               = 'tag.property.price3';
        const TAG_PROPERTY_CONSTRUCTION         = 'tag.property.construction';
        const TAG_PROPERTY_FLOORPLAN            = 'tag.property.floor_plan';
        const TAG_PROPERTY_BUILDINGAREA         = 'tag.property.building_area';
        const TAG_PROPERTY_HIERARCHY            = 'tag.property.hierarchy';
        const TAG_PROPERTY_SERCURITYDEPOSIT     = 'tag.property.sercurity_deposit';
        const TAG_PROPERTY_DEPOSIT              = 'tag.property.deposit';
        const TAG_PROPERTY_ADMINISTRATIONFEE    = 'tag.property.administration_fee';
        const TAG_PROPERTY_USEDPARTIALAREA      = 'tag.property.used_partial_area';
        const TAG_PROPERTY_LANDAREA             = 'tag.property.land_area';
        const TAG_PROPERTY_KEYMONEY             = 'tag.property.key_money';
        const TAG_PROPERTY_TSUBAUNITPRICE       = 'tag.property.tsuba_unit_price';
        const TAG_PROPERTY_USAGEAREA            = 'tag.property.usage_area';
        const TAG_PROPERTY_BASISNUMBER          = 'tag.property.basis_number';
        const TAG_PROPERTY_NAME                 = 'tag.property.name';
        const TAG_PROPERTY_CONSTRUCTIONDATE     = 'tag.property.construction_date';
        const TAG_PROPERTY_BUILDINGSTRUCTURE    = 'tag.property.building_structure';
        const TAG_PROPERTY_REALESTATEURL        = 'tag.property.realestate_url';
        const TAG_PROPERTY_NEW                  = 'tag.property.new';
        const TAG_PROPERTY_COMMENT              = 'tag.property.comment';
        const TAG_PROPERTY_WAYSIDE              = 'tag.property.wayside';
        const TAG_PROPERTY_STATION              = 'tag.property.station';
        const TAG_PROPERTY_IMAGE_KOMA           = 'tag.property.image_koma';
        const TAG_SP_GLONAVI                    = 'tag.sp_glonavi';
        const CONVERT_MAN_TO_INT                = 10000;
        
        protected $_list = array(
          self::CATEGORY_TAG_SITE => array (
//              self::TAG_GOOGLE_MAP            => array('GoogleMap', 'google_map'),
              self::TAG_TWITTER               => array('Twitter', 'tw_1'),
              self::TAG_FACEBOOK              => array('facebook', 'fb_1'),
              self::TAG_LINE                  => array('LINE（スマホのみ掲載）', 'line_1'),
              self::TAG_SITE_URL              => array('サイトURL', 'site_url'),
              self::TAG_SITEMAP               => array('サイトマップ', 'sitemap'),
              self::TAG_SITE_NAME             => array('サイト名', 'site_name'),
              self::TAG_SITE_DESCRIPTION      => array('サイトの説明', 'site_description'),
              self::TAG_SITE_KEYWORD          => array('キーワード', 'keyword'),
              self::TAG_PAGE_TITLE            => array('ページタイトル', 'page_title'),
              self::TAG_PAGE_DESCRIPTION      => array('ページの説明<span>（配下ページ）</span>', 'page_description'),
              self::TAG_PAGE_KEYWORD          => array('ページのキーワード<br><span>（配下ページ）</span>', 'page_keyword'),
            //   self::TAG_PAGE_NAME             => array('ページ名<span>（パーマリンク）</span>', 'page_name'),
              self::TAG_NEWS_LIST1             => array('お知らせ1一覧へのリンクURL', 'news_list1'),
              self::TAG_NEWS_LIST2             => array('お知らせ2一覧へのリンクURL', 'news_list2'),
              self::TAG_ELEMENT_ARTICLELINK   => array('不動産お役立ち情報のリンク', 'article_link2'),
              self::TAG_ELEMENT_GOOGLE_MAP_API_KEY             => array('Googlemap APIキー','google_map_api_key'),
          ),
          self::CATEGORY_TAG_COMPONENT => array (
              self::TAG_COMPONENT_HEADABOVE     => array('</head>直上タグ', 'head_above'),
              self::TAG_COMPONENT_BODYUNDER     => array('<body>直下タグ', 'body_under'),
              self::TAG_COMPONENT_BODYABOVE     => array('</body>直上タグ', 'body_above')
          ),
          self::CATEGORY_TAG_PROPERTY => array (
              self::TAG_PROPERTY_TYPE               => array('物件種目', 'property_type'),
              self::TAG_PROPERTY_IMAGE1             => array('画像1（間取図/平面図）', 'image1'),
              self::TAG_PROPERTY_IMAGE2             => array('画像2（外観）', 'image2'),
              self::TAG_PROPERTY_IMAGE3             => array('画像3', 'image3'),
              self::TAG_PROPERTY_IMAGE4             => array('画像4', 'image4'),
              self::TAG_PROPERTY_TRAFFIC            => array('交通', 'traffic'),
              self::TAG_PROPERTY_LOCATION           => array('所在地', 'location'),
              self::TAG_PROPERTY_STATIONWALKING     => array('駅徒歩', 'station_walking'),
              self::TAG_PROPERTY_RENT               => array('賃料', 'rent'),
              self::TAG_PROPERTY_PRICE1             => array('価格<span>（単位込み→万円）</span>', 'price1'),
              self::TAG_PROPERTY_PRICE2             => array('価格<span>（単位なし→価格のみ）</span>', 'price2'),
              self::TAG_PROPERTY_PRICE3             => array('価格<span>（単位なし→価格のみ<br>（万円を数字に変換））</span>', 'price3'),
//              self::TAG_PROPERTY_CONSTRUCTION       => array('構造', 'construction'),
              self::TAG_PROPERTY_FLOORPLAN          => array('間取り', 'floor_plan'),
              self::TAG_PROPERTY_BUILDINGAREA       => array('建物面積', 'building_area'),
              self::TAG_PROPERTY_HIERARCHY          => array('階建/階', 'hierarchy'),
              self::TAG_PROPERTY_SERCURITYDEPOSIT   => array('敷金', 'security_deposit'),
              self::TAG_PROPERTY_DEPOSIT            => array('保証金', 'deposit'),
              self::TAG_PROPERTY_ADMINISTRATIONFEE   => array('管理費等', 'administration_fee'),
              self::TAG_PROPERTY_USEDPARTIALAREA    => array('専有面積', 'used_partial_area'),
              self::TAG_PROPERTY_LANDAREA           => array('土地面積', 'land_area'),
              self::TAG_PROPERTY_KEYMONEY           => array('礼金', 'key_money'),
              self::TAG_PROPERTY_TSUBAUNITPRICE     => array('坪単価', 'tsubo_unit_price'),
              self::TAG_PROPERTY_USAGEAREA          => array('用途地域', 'usage_area'),
              self::TAG_PROPERTY_BASISNUMBER        => array('坪数', 'basis_number'),
              self::TAG_PROPERTY_NAME               => array('建物名', 'name'),
              self::TAG_PROPERTY_CONSTRUCTIONDATE   => array('築年月', 'construction_date'),
              self::TAG_PROPERTY_BUILDINGSTRUCTURE  => array('建物構造', 'building_structure'),
              self::TAG_PROPERTY_REALESTATEURL      => array('物件詳細へのリンクURL', 'realestate_url'),
              self::TAG_PROPERTY_NEW                => array('新着', 'new'),
              self::TAG_PROPERTY_COMMENT            => array('おすすめコメント', 'comment'),
              self::TAG_PROPERTY_WAYSIDE            => array('沿線名', 'wayside'),
              self::TAG_PROPERTY_STATION            => array('駅名', 'station'),
              self::TAG_PROPERTY_IMAGE_KOMA         => array('画像自動<span>（2→1→3→4→5→...）</span>', 'image_koma'),
            ),
          self::CATEGORY_TAG_NEWS => array (
              self::TAG_NEWS_DETAIL           => array('お知らせ詳細へのリンクURL', 'news_detail'),
              self::TAG_NEWS_TITLE            => array('一覧タイトル', 'news_title'),
              self::TAG_NEWS_DATE             => array('日付（年月日）', 'date'),
              self::TAG_NEWS_YEAR             => array('日付（年）', 'year'),
              self::TAG_NEWS_MONTH            => array('日付（月）', 'month'),
              self::TAG_NEWS_DAILY            => array('日付（日）', 'daily'),
              // self::TAG_NEWS_TIME             => array('日付(時)', 'time'),
              // self::TAG_NEWS_MINUTE           => array('日付(分)', 'minute'),
              self::TAG_NEWS_WEEK1            => array('曜日（日本語-正式）', 'week1'),
              self::TAG_NEWS_WEEK2            => array('曜日（日本語-略）', 'week2'),
              self::TAG_NEWS_WEEK3            => array('曜日（英語-正式）', 'week3'),
              self::TAG_NEWS_WEEK4            => array('曜日（英語-略）', 'week4'),
              self::TAG_NEWS_TEXT             => array('お知らせ本文テキスト', 'text'),
              self::TAG_NEWS_CATEGORY         => array('カテゴリー', 'category'),
              self::TAG_NEWS_CATEGORY_CLASS   => array('class名を返す', 'category_class'),
              self::TAG_NEWS_NEW_MARK         => array('NEWマーク', 'new_mark'),
          ),
          self::CATEGORY_TAG_ELEMENT => array(
              self::TAG_ELEMENT_NEWS1         => array('お知らせ１','news1_1'),
              self::TAG_ELEMENT_NEWS2         => array('お知らせ2','news2_1'),
              self::TAG_ELEMENT_CHINTAI       => array('賃貸物件検索リンク（サイドエリア）','chintai_2'),
              self::TAG_ELEMENT_BUY           => array('売買物件検索リンク（サイドエリア）','buy_2'),
              self::TAG_ELEMENT_SIDELINK      => array('その他サイドリンク','side_link2'),
              self::TAG_ELEMENT_CUSTOMIZE     => array('カスタマイズサイドコンテンツ','customize2'),
          ),
          // NHP-3751:フリーワード検索をオリジナルタグとしてHTMLに組み込めるようにする
          self::CATEGORY_TAG_FREEWORD_PARTS => array(
              self::TAG_FREEWORD_FORM  => array('フリーワード検索(フル)','fw_fullform'),
              self::TAG_FREEWORD_TYPESEL      => array('種別選択','fw_type'),
              self::TAG_FREEWORD_TYPE_RESIDENTIAL_CHINTAI => array('種別選択(居住用賃貸)', 'fw_type_residential_chintai'),
              self::TAG_FREEWORD_TYPE_BUSINESS_CHINTAI    => array('種別選択(事業用賃貸)', 'fw_type_business_chintai'),
              self::TAG_FREEWORD_TYPE_RESIDENTIAL_BUY     => array('種別選択(居住用売買)', 'fw_type_residential_buy'),
              self::TAG_FREEWORD_TYPE_BUSINESS_BUY        => array('種別選択(事業用売買)', 'fw_type_business_buy'),
              self::TAG_FREEWORD_TEXT         => array('検索文字列入力','fw_text'),
              self::TAG_FREEWORD_COUNTER      => array('検索物件数','fw_counter'),
              self::TAG_FREEWORD_BUTTON       => array('検索ボタン','fw_button'),
          ),
        );
        
        public function getTitle($key, $category='') {
            if (!$category) 
                return isset($this->_list[$key]) ? $this->_list[$key][0] : "[invalid::{$key}]";
            
            return isset($this->_list[$category][$key]) ? $this->_list[$category][$key][0] : "[invalid::{$key}]";
        }
        
        public function getTag($key, $category='') {
            if (!$category) 
                return isset($this->_list[$key]) ? $this->_list[$key][1] : "[invalid::{$key}]";
            
            return isset($this->_list[$category][$key]) ? $this->_list[$category][$key][1] : "[invalid::{$key}]";
        }

        public function getTags($key, $category = ''){
            if (!$category)
                return isset($this->_list[$key]) ? $this->_list[$key] : "[invalid::{$key}]";

            return isset($this->_list[$category][$key]) ? $this->_list[$category][$key] : "[invalid::{$key}]";
        }

        public function getValueTags($key, $category =''){
            $tags = array_values($this->getTags($key, $category));
            $data = [];
           foreach($tags as $k => $v){
               $data[$v[1]] = $v[0];
           }
           return $data;
        }

        public function getValueTagsWithChunk( $number, $key,$category = '', $preserveKeys = true){
            return array_chunk(
                $this->getValueTags($key,$category),
                $number,
                $preserveKeys
            );
        }

        public function getSpGloNavi() {
          return array('グロナビ（スマホ）', 'sp_glonavi');
        }
    }