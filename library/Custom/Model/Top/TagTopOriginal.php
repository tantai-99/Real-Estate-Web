<?php
namespace Library\Custom\Model\Top;

use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Publish\Render;
use Library\Custom\Model\Lists;
use Library\Custom\Model\Estate;
use Library\Custom\View\Helper;

class TagTopOriginal {
    
    const TIMEOUT = 20;
    
    const GLONAVI_URL               = 'glonavi_url';
    const GLONAVI_LABEL             = 'glonavi_label';
    const TAG_SITE_URL              = 'site_url';
    const TAG_SITEMAP               = 'sitemap';
    const TAG_SITE_NAME             = 'site_name';
    const TAG_SITE_DESCRIPTION      = 'site_description';
    const TAG_SITE_KEYWORD          = 'keyword';
    const TAG_PAGE_TITLE            = 'page_title';
    const TAG_PAGE_DESCRIPTION      = 'page_description';
    const TAG_PAGE_KEYWORD          = 'page_keyword';
    // const TAG_PAGE_NAME             = 'page_name';

    const TAG_CHINTAI               = 'chintai_2';
    const TAG_BUY                   = 'buy_2';
    const TAG_SIDELINK              = 'side_link2';
    const TAG_SIDELINK_SCRIPT       = 'side_link2_script';
    const TAG_CUSTOMIZE             = 'customize2';
    const TAG_NEWS_1                = 'news1_1';
    const TAG_NEWS_2                = 'news2_1';
    const TAG_ARTICLELINK           = 'article_link2';
    const TAG_GOOGLE_MAP_API_KEY        = 'google_map_api_key';

    const TAG_NEWS_DETAIL          = 'news_detail';
    const TAG_NEWS_LIST1             = 'news_list1';
    const TAG_NEWS_LIST2             = 'news_list2';
    const TAG_NEWS_TITLE            = 'news_title';
    const TAG_NEWS_DATE             = 'date';
    const TAG_NEWS_YEAR             = 'year';
    const TAG_NEWS_MONTH            = 'month';
    const TAG_NEWS_DAILY            = 'daily';
    const TAG_NEWS_WEEK1            = 'week1';
    const TAG_NEWS_WEEK2            = 'week2';
    const TAG_NEWS_WEEK3            = 'week3';
    const TAG_NEWS_WEEK4            = 'week4';
    const TAG_NEWS_TEXT             = 'text';
    const TAG_NEWS_CATEGORY         = 'category';
    const TAG_NEWS_CATEGORY_CLASS   = 'category_class';
    const TAG_NEWS_NEW_MARK         = 'new_mark';

    // NHP-3751:フリーワード検索をオリジナルタグとしてHTMLに組み込めるようにする
    const TAG_FREEWORD_FORM         = 'fw_fullform';
    const TAG_FREEWORD_TYPE         = 'fw_type';
    const TAG_FREEWORD_TYPE_RESIDENTIAL_CHINTAI = 'fw_type_residential_chintai';
    const TAG_FREEWORD_TYPE_BUSINESS_CHINTAI    = 'fw_type_business_chintai';
    const TAG_FREEWORD_TYPE_RESIDENTIAL_BUY     = 'fw_type_residential_buy';
    const TAG_FREEWORD_TYPE_BUSINESS_BUY        = 'fw_type_business_buy';
    const TAG_FREEWORD_TEXT         = 'fw_text';
    const TAG_FREEWORD_COUNTER      = 'fw_counter';
    const TAG_FREEWORD_BUTTON       = 'fw_button';

    const TAG_HEAD_ABOVE            = 'head_above';
    const TAG_BODY_UNDER            = 'body_under';
    const TAG_BODY_ABOVE            = 'body_above';

    const TAG_GOOGLE_MAP            = 'google_map';
    const TAG_TWITTER               = 'tw_1';
    const TAG_FACEBOOK              = 'fb_1';
    const TAG_LINE                  = 'line_1';
        
    const TAG_PROPERTY_TYPE                 = 'property_type';
    const TAG_PROPERTY_IMAGE1               = 'image1';
    const TAG_PROPERTY_IMAGE2               = 'image2';
    const TAG_PROPERTY_IMAGE3               = 'image3';
    const TAG_PROPERTY_IMAGE4               = 'image4';
    const TAG_PROPERTY_TRAFFIC              = 'traffic';
    const TAG_PROPERTY_LOCATION             = 'location';
    const TAG_PROPERTY_STATIONWALKING       = 'station_walking';
    const TAG_PROPERTY_RENT                 = 'rent';
    const TAG_PROPERTY_PRICE1               = 'price1';
    const TAG_PROPERTY_PRICE2               = 'price2';
    const TAG_PROPERTY_PRICE3               = 'price3';
    const TAG_PROPERTY_CONSTRUCTION         = 'construction';
    const TAG_PROPERTY_FLOORPLAN            = 'floor_plan';
    const TAG_PROPERTY_BUILDINGAREA         = 'building_area';
    const TAG_PROPERTY_HIERARCHY            = 'hierarchy';
    const TAG_PROPERTY_SERCURITYDEPOSIT     = 'security_deposit';
    const TAG_PROPERTY_DEPOSIT              = 'deposit';
    const TAG_PROPERTY_ADMINISTRATIONFEE    = 'administration_fee';
    const TAG_PROPERTY_USEDPARTIALAREA      = 'used_partial_area';
    const TAG_PROPERTY_LANDAREA             = 'land_area';
    const TAG_PROPERTY_KEYMONEY             = 'key_money';
    const TAG_PROPERTY_TSUBAUNITPRICE       = 'tsubo_unit_price';
    const TAG_PROPERTY_USAGEAREA            = 'usage_area';
    const TAG_PROPERTY_BASISNUMBER          = 'basis_number';
    const TAG_PROPERTY_NAME                 = 'name';
    const TAG_PROPERTY_CONSTRUCTIONDATE     = 'construction_date';
    const TAG_PROPERTY_BUILDINGSTRUCTURE    = 'building_structure';
    const TAG_PROPERTY_REALESTATEURL        = 'realestate_url';
    const TAG_PROPERTY_NEW                  = 'new';
    const TAG_PROPERTY_COMMENT              = 'comment';
    const TAG_PROPERTY_WAYSIDE              = 'wayside';
    const TAG_PROPERTY_STATION              = 'station';
    const TAG_PROPERTY_IMAGE_KOMA           = 'image_koma';

    const SCRIPT_SITE_NAME                   = '<?php echo $site_name ?>';    
    const SCRIPT_SITE_DESCRIPTION            = '<?php echo $site_description ?>';    
    const SCRIPT_SITE_KEYWORD                = '<?php echo $site_keyword ?>';    
    const SCRIPT_PAGE_TILTE                  = '<?php echo $page_title ?>';    
    const SCRIPT_PAGE_DESCRIPTION            = '<?php echo $page_description ?>';    
    const SCRIPT_PAGE_KEYWORD                = '<?php echo $page_keyword ?>';    
    /*const SCRIPT_PAGE_NAME                   = '<?php echo $page_name ?>';*/
    const SP_GLONAVI                         = 'sp_glonavi';

    const NON_DISPLAY_ELEMENT                = 'non-display-corresponding-element';

    protected $_list = array();
    protected $_list_koma = array();
    protected $_list_news = array();
    public function __construct() {
        if(app('request')->hasSession())
        {
            $this->session = app('request')->session()->get('publish');
        }
        $this->_config = getConfigs('v1api.api');
    }

    protected $_deviceTagList = array();

    public function setTagList($hp, $publishType, $page, $device, $pages) {

        if(!isset($this->_deviceTagList[$device])) {

            $this->_deviceTagList[$device] = array();

            $siteUrl = Render\AbstractRender::protocol($publishType).Render\AbstractRender::www($publishType).Render\AbstractRender::prefix($publishType).$this->session->company->domain;
            $company = \App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hp->id);
            $tag = $company->fetchTag();
            $gNavs = $hp->getGlobalNavigation($hp->global_navigation)->toArray();
            $estateSetting = $hp->getEstateSetting();
            // update data gnavs
            foreach ($gNavs as $key => $gNav) {
                $gNavs[$key] = $pages[$gNav['id']];
            }
            foreach ($gNavs as $key=>$_page) {
                if ($device == 'pc' || $device == 'sp') {
                    // Not display navigation when preview (new_flg = 1) and publish (public_flg = 0)
                    if (($publishType === config('constants.publish_type.TYPE_PREVIEW') && $_page['new_flg'] == 1) || ($publishType !== config('constants.publish_type.TYPE_PREVIEW') && $_page['public_flg'] == 0)) {
                        continue;
                    }

                    if ($_page['page_type_code'] == HpPageRepository::TYPE_LINK) {
                        $this->_deviceTagList[$device][self::GLONAVI_URL.($key + 1)]     = Lists\Original::getPageFileName($_page,$estateSetting) . '/';
                    }
                     else {
                        if (Lists\Original::getPageFileName($_page,$estateSetting) == "") {
                            $this->_deviceTagList[$device][self::GLONAVI_URL.($key + 1)]     = $siteUrl;
                        } else {
                            $this->_deviceTagList[$device][self::GLONAVI_URL.($key + 1)]     = implode('/', array($siteUrl, Lists\Original::getPageFileName($_page,$estateSetting))) . '/';
                        }
                    }
                    $this->_deviceTagList[$device][self::GLONAVI_LABEL.($key + 1)]   = Lists\Original::getPageTitle($_page,$estateSetting,false);
                }
            }
            $keywords = array_diff(explode(',', $hp->keywords), array(''));
            $siteKeyword = implode(',', $keywords);

            // NHP-3751:フリーワード検索
            $freewordTag = $this->_makeFreewordTags($estateSetting, $hp->original_search);
            $googleMapApiKey = $hp->fetchCompanyRow()->google_map_api_key;
            $this->_deviceTagList[$device] = array_merge($this->_deviceTagList[$device], array(
                self::TAG_SITE_URL              => $siteUrl,
                self::TAG_SITE_NAME             => $hp->title,
                self::TAG_SITE_DESCRIPTION      => $hp->description,
                self::TAG_SITE_KEYWORD          => $siteKeyword,
                // self::TAG_PAGE_NAME             => $page['title'],
                self::TAG_HEAD_ABOVE            => $tag ? $tag->google_analytics_code.$tag->above_close_head_tag : '',
                self::TAG_BODY_UNDER            => $tag ? $tag->under_body_tag : '',
                self::TAG_BODY_ABOVE            => $tag ? $tag->above_close_body_tag : '',

                // NHP-3751:フリーワード検索
                self::TAG_FREEWORD_FORM         => $freewordTag['fwFullform'], 
                self::TAG_FREEWORD_TYPE         => $freewordTag['fwType'],
                self::TAG_FREEWORD_TYPE_RESIDENTIAL_CHINTAI => $freewordTag['fwTypeReidentialChintai'], 
                self::TAG_FREEWORD_TYPE_BUSINESS_CHINTAI    => $freewordTag['fwTypeBusinessChintai'],
                self::TAG_FREEWORD_TYPE_RESIDENTIAL_BUY     => $freewordTag['fwTypeReidentialBuy'],
                self::TAG_FREEWORD_TYPE_BUSINESS_BUY        => $freewordTag['fwTypeBusinessBuy'],
                self::TAG_FREEWORD_TEXT         => $freewordTag['fwText'],
                self::TAG_FREEWORD_COUNTER      => $freewordTag['fwCounter'],
                self::TAG_FREEWORD_BUTTON       => $freewordTag['fwButton'],
                self::TAG_GOOGLE_MAP_API_KEY    => $googleMapApiKey,
            ) );
        }

        $this->_partElementTagList = array(
            self::TAG_PAGE_TITLE            => $page['title'],
            self::TAG_PAGE_DESCRIPTION      => implode(':', array($page['title'], $this->_deviceTagList[$device][self::TAG_SITE_DESCRIPTION])),
            self::TAG_PAGE_KEYWORD          => implode(',', array($page['title'], $this->_deviceTagList[$device][self::TAG_SITE_KEYWORD])),
        ); 
        $this->_partElementTagList = array_merge($this->_partElementTagList, $this->_deviceTagList[$device]);

        $this->_list = array_merge($this->_list, $this->_partElementTagList);
    }

    public function setTagSocialNetwork($socials)
    {
       if (!is_array($socials)) {
            throw new \Exception ('News info list must be array.');
        }
        foreach ($socials as $tag => $html) {
            $this->_list[$tag] = $html;
        } 
    }
    
    public function setTagInfoList($infoList)
    {
        if (!is_array($infoList)) {
            throw new \Exception ('News info list must be array.');
        }
        
        $this->_list_news = $infoList;
        foreach ($infoList as $tag => $infos) {
            $this->_list[$tag] = $infos;
        }
    }

    public function setTagCommon($tagCommon) {
        foreach ($tagCommon as $key=>$value) {
            $this->_list[$key] = $value;
        }
    }
    public function setPartCommon($partCommon) {
        foreach ($partCommon as $key=>$value) {
            $this->_list[$key] = $value;
        }
    }

    public function setTag($tagname, $output) {
        $this->_list[$tagname] = $output;
    }

    public function setTagKoma($params,$review=false) {
    
        if (!empty($params)) {
        
            if($review==true){
            
                $_helperTags = Helper\Tags::getInstance();
                $temp = array(
                            self::TAG_PROPERTY_TYPE                 => '新築貸マンション',
                            self::TAG_PROPERTY_IMAGE1               => 'https://stg-img3.athome.jp//image_files/path/zVL5s7clfTFVOKdHSU5UEZjvrRzsrcNk',
                            self::TAG_PROPERTY_IMAGE2               => 'https://stg-img3.athome.jp//image_files/path/zVL5s7clfTGGg6oYjlPif1vMSpxmJiE-',
                            self::TAG_PROPERTY_IMAGE3               => 'https://stg-img3.athome.jp//image_files/path/zVL5s7clfTGgMSyD6tEC33QZX23Tnnmt',
                            self::TAG_PROPERTY_IMAGE4               => 'https://stg-img3.athome.jp//image_files/path/zVL5s7clfTECeOShx6x_Qp6zj11ix2Zx',
                            self::TAG_PROPERTY_TRAFFIC              => '東京メトロ丸ノ内線 / 新宿三丁目駅 徒歩7分',
                            self::TAG_PROPERTY_LOCATION             => '新宿区新宿６丁目',
                            self::TAG_PROPERTY_STATIONWALKING       => '徒歩7分',
                            self::TAG_PROPERTY_RENT                 => '12.9<span>万円</span>',
                            self::TAG_PROPERTY_PRICE1               => '12.9万円',
                            self::TAG_PROPERTY_PRICE2               => '12.9',
                            self::TAG_PROPERTY_PRICE3               => '129,000',
                         //   self::TAG_PROPERTY_CONSTRUCTION         => 'ＲＣ',
                            self::TAG_PROPERTY_FLOORPLAN            => '1K',
                            self::TAG_PROPERTY_BUILDINGAREA         => '25.17㎡',
                            self::TAG_PROPERTY_HIERARCHY            => '7階建/5階',
                            self::TAG_PROPERTY_SERCURITYDEPOSIT     => self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)],
                            self::TAG_PROPERTY_DEPOSIT              => self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)],
                            self::TAG_PROPERTY_ADMINISTRATIONFEE     => '3,000円',
                            self::TAG_PROPERTY_USEDPARTIALAREA      => '41.02㎡',
                            self::TAG_PROPERTY_LANDAREA             => '-',
                            self::TAG_PROPERTY_KEYMONEY             => self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)],
                            self::TAG_PROPERTY_TSUBAUNITPRICE       => '0.93万円',
                            self::TAG_PROPERTY_USAGEAREA            => '-',
                            self::TAG_PROPERTY_BASISNUMBER          => '12.40坪',
                            self::TAG_PROPERTY_BUILDINGSTRUCTURE    => 'ＲＣ',
                            self::TAG_PROPERTY_CONSTRUCTIONDATE     => '1982年6月(築36年7ヶ月)',
                            self::TAG_PROPERTY_NAME                 => '渋谷本町マンション 2階 1LDK',
                            self::TAG_PROPERTY_REALESTATEURL        => '/chintai/detail-5b8f7022fa2eb70b46821f1b/',
                            self::TAG_PROPERTY_NEW                  => self::newArrayDefault()[rand(0, count(self::newArrayDefault())-1)],
                            self::TAG_PROPERTY_COMMENT              => '♪エレベーター♪オートロック♪インターネット光対応（フレッツ光：マンションタイプ）♪自転車置場♪ダイヤルポスト♪バス・トイレ別♪エアコン１基♪多機能便座♪洋室照明付き♪玄関ディンプルキー■積水ハウス施',
                            self::TAG_PROPERTY_WAYSIDE              => '東京メトロ有楽町線',
                            self::TAG_PROPERTY_STATION              => '月島駅',
                            self::TAG_PROPERTY_IMAGE_KOMA           => 'https://stg-img3.athome.jp//image_files/path/zVL5s7clfTFVOKdHSU5UEZjvrRzsrcNk',
                        );
                foreach ($params as $param) {
                    
                    if (null == $param) continue;
                    
                    $this->_list['special_'.$param['id']]='';
                    $html_file='bukken_koma/special'.$param['id'].'_'.$param['device'].'.html';
                    $this->_list['special_'.$param['id']] = '<div class="top__bukkenkoma koma__special_'.$param['id'].'">';
                    for ($i = 0; $i < $param['rows']*$param['columns']; $i++) {
                        if ($i == 0) {
                            $this->_list['special_'.$param['id']].= '<div class="koma__detail top__bukenkoma_1">';
                        }
                        if ($i != 0 && $i%$param['columns'] == 0) {
                            $this->_list['special_'.$param['id']].= '</div>';
                            $this->_list['special_'.$param['id']].= '<div class="koma__detail top__bukenkoma_'.($i/$param['columns'] + 1).'">';
                        }
                        $_helperTags->newBlock($html_file);
                        $temp[self::TAG_PROPERTY_SERCURITYDEPOSIT]=self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)];
                        $temp[self::TAG_PROPERTY_DEPOSIT]=self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)];
                        $temp[self::TAG_PROPERTY_KEYMONEY]=self::monneyArrayDefault()[rand(0, count(self::monneyArrayDefault())-1)];
                        $temp[self::TAG_PROPERTY_NEW]=self::newArrayDefault()[rand(0, count(self::newArrayDefault())-1)];

                        foreach ($temp as $key => $value) {
                            $_helperTags->assign($key,$value);
                        }
                        $this->_list['special_'.$param['id']].=$_helperTags->getOutput();
                    }
                    $this->_list['special_'.$param['id']].= '</div></div>';
                }
            }
            else{
            
                foreach ($params as $param) {
                
                    $this->_list['special_'.$param['id']] = '<?php $this->viewHelper->includeCommonFile("special'.$param['id'].'"); ?>';
                    $this->_list_koma[]='special'.$param['id'];
                }
            }
        }
    }

    public function setNoficationTagList() {

    }
    public function getListTagKoma() {
        return $this->_list_koma;
    }
    public function getListTags() {
        return $this->_list;
    }

    public function getTag($name, $default = false) {
        if (isset($this->_list[$name])) {
            return $this->_list[$name];
        } else {
            return $default;
        }
    }

    public static function monneyArrayDefault() {
        return array('1ヶ月','なし','5.00万円');
    }

    public static function newArrayDefault() {
        return array('<span>NEW</span>', '');
    }
    
    public function setHeaderPublish(){
        $this->_list[self::TAG_SITE_NAME]       	=	self::SCRIPT_SITE_NAME;
        $this->_list[self::TAG_SITE_DESCRIPTION]	=	self::SCRIPT_SITE_DESCRIPTION;
        $this->_list[self::TAG_SITE_KEYWORD]		=	self::SCRIPT_SITE_KEYWORD;
        $this->_list[self::TAG_PAGE_TITLE]			=	self::SCRIPT_PAGE_TILTE;
        $this->_list[self::TAG_PAGE_DESCRIPTION]	=	self::SCRIPT_PAGE_DESCRIPTION;
        $this->_list[self::TAG_PAGE_KEYWORD]		=	self::SCRIPT_PAGE_KEYWORD;
        // $this->_list[self::TAG_PAGE_NAME]		    =	self::SCRIPT_PAGE_NAME;
    }

    public function setTagSpGlonavi($spGlonavi = null) {
        $this->_list[self::SP_GLONAVI] = $spGlonavi;
    }

    public function addScriptHeader(){

        return <<< 'EOD'
<?php 
if($head){
    preg_match('/<title>(.*)<\/title>/i',$head,$title_match);
    preg_match('/keywords(.*)content=[\'"](.*)[\'"]/i',$head,$keywords_match);
    preg_match('/description(.*)content=[\'"](.*)[\'"]/i',$head,$description_match);

    $thisPage['title'] = array_pop($title_match);
    $thisPage['description'] = array_pop($keywords_match);
    $thisPage['keyword'] = array_pop($description_match);
}
$db       = debug_backtrace();
$viewHelper = new ViewHelper($this->_view);
$hp       = unserialize($viewHelper->getContentSettingFile('hp.txt'));
$pages    = unserialize($viewHelper->getContentSettingFile('pages.txt'));
$filename = $viewHelper->getFileName(dirname($db[1]['file']));
$site_name              =  $hp['title'];
$site_description       =  $hp['description'];
$keywords = array_diff(explode(',', $hp['keywords']), array(''));
$site_keyword           =  implode(',', $keywords);
if($thisPage){
    $page_title             =  $h1? strip_tags($h1):$thisPage['title'];
    $page_description       =  $h1? strip_tags($h1).':'.$hp['description']:$thisPage['title'].':'.$hp['description'];
    $page_keyword           =  $h1? strip_tags($h1).','.$site_keyword:$thisPage['title'].','.$site_keyword;
    //$page_name              =  $h1? strip_tags($h1):$thisPage['title'];
}
else{
    $thisPage = $viewHelper->getPageByFileName($filename);
    if ($filename == 'pc' || $filename == 'sp') {
        foreach ($pages as $page) {
            if ($page['page_type_code'] == 1) {
                $thisPage = $page;
                break;
            }
        }
    }
    //sitemap
    elseif ($filename === 'sitemap') {
        $thisPage['title'] = 'サイトマップ';
    }
    // 404
    elseif ($filename === '404notFound') {
        $thisPage['title'] = 'ページが見つかりません';
    }
    $page_title             =  $thisPage['title'];
    $page_description       =  $thisPage['title'].':'.$hp['description'];
    $page_keyword           =  $thisPage['title'].','.$site_keyword;
    //$page_name              =  $thisPage['title'];
}
ob_start();
?>
EOD;
    }

    
    public function addScriptAfterHeader(){
        $html = <<< 'EOD'
        <?php 
            $html = ob_get_contents();
            ob_end_clean();
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_use_internal_errors(false);
            $selector = new DOMXPath($doc);
            if(isset($thisPage['is_inquiry']) && $thisPage['is_inquiry']) {
                foreach($selector->query('//*[contains(attribute::class, "{class}" )]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
                foreach($selector->query('//*[contains(attribute::id, "{class}" )]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
                $html = $doc->saveHTML($doc->documentElement);
            }
            echo $html;

        ?>
EOD;
        return str_replace('{class}',self::NON_DISPLAY_ELEMENT,$html);
    }


    public function filterHeaderPreview($html){
        if(!$html) return null;
        $class = self::NON_DISPLAY_ELEMENT;
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_use_internal_errors(false);
        $selector = new \DOMXPath($doc);
        foreach($selector->query('//*[contains(attribute::class, "'.$class.'" )]') as $e ) {
            $e->parentNode->removeChild($e);
        }
        return $doc->saveHTML($doc->documentElement);
    }

    public function addScriptBeforeFooter(){
        return <<< 'EOD'
<?php
$db       = debug_backtrace();
$viewHelper = new ViewHelper($this->_view);
$hp       = unserialize($viewHelper->getContentSettingFile('hp.txt'));
$pages    = unserialize($viewHelper->getContentSettingFile('pages.txt'));
$filename = $viewHelper->getFileName(dirname($db[1]['file']));
if(!$thisPage){
    $thisPage = $viewHelper->getPageByFileName($filename);
}
ob_start();
?>
EOD;
    }

    /**
     * inherit from script after header
     * @return mixed
     */
    public function addScriptAfterFooter(){
        $html = <<< 'EOD'
        <?php 
            $html = ob_get_contents();
            ob_end_clean();
            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_use_internal_errors(false);
            $selector = new DOMXPath($doc);
            if(isset($thisPage['is_inquiry']) && $thisPage['is_inquiry']) {
                foreach($selector->query('//*[contains(attribute::class, "{class}" )]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
                foreach($selector->query('//*[contains(attribute::id, "{class}" )]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
                foreach($selector->query('//div[contains(@class, "guide-nav" )]') as $e ) {
                    $e->parentNode->removeChild($e);
                }
                $html = $doc->saveHTML($doc->documentElement);
            }
            echo $html;

        ?>
EOD;
        return str_replace('{class}',self::NON_DISPLAY_ELEMENT,$html);
    }

    /**
     * inherit from header preview
     * @param $html
     * @return null|string
     */
    public function filterFooterPreview($html){
        return $this->filterHeaderPreview($html);
    }

    /**
     * NHP-3751:フリーワード検索のパーツを生成
     * @param $estateSetting
     * @return hash
     */
    private function _makeFreewordTags($estateSetting=null, $hp_original_search=null) {
        // 1.種別選択フォームの生成
        $fullOptions = [];
        $eachOptions = [];

        if(is_null($hp_original_search)) {
            $original_search = [
                [ 'type_no' => 0, 'display_flg' => 1, 'display_name' => '選択してください', 'place_holder' => '種別を選択してください' ],
                [ 'type_no' => 1, 'display_flg' => 1, 'display_name' => '居住用賃貸', 'place_holder' => '例：12.2万円以下 和室' ],
                [ 'type_no' => 2, 'display_flg' => 1, 'display_name' => '事業用賃貸', 'place_holder' => '例：12.2万円以下 駐車場あり' ],
                [ 'type_no' => 3, 'display_flg' => 1, 'display_name' => '居住用売買', 'place_holder' => '例：2000万円以下 南向き' ],
                [ 'type_no' => 4, 'display_flg' => 1, 'display_name' => '事業用売買', 'place_holder' => '例：2000万円以下 駐車場あり' ],
            ];
        } else {
            $original_search = json_decode($hp_original_search, true);
        }
       
        if(!empty($estateSetting)) {
            $searchClasses = Estate\ClassList::getInstance()->getAll();
            foreach($original_search as $k=>$v) {
				if($v['type_no'] == 0) {
                    continue;
                }
                $class = $v['type_no'];

                $each[ $class ] = null;

				$searchSetting = $estateSetting->getSearchSetting($class);
                if(empty($searchSetting['enabled_estate_type'])) {
                    continue;
                }
                if (!isset($searchSetting) || $searchSetting['display_freeword'] != 1) {
                    continue;
                }
                $type = explode(',', $searchSetting['enabled_estate_type']);
                if(count($type) == 1) {
                    $valueSearch = $type[0];
                } else {
                    $valueSearch = Estate\TypeList::getInstance()->getCompositeType($type);
                }
                $typeSearch = Estate\TypeList::getInstance()->getUrl($valueSearch);

                $typeOption = sprintf("<option value=\"%s\" alias_name=\"%s\" type_placeholder=\"%s\">%s</option>",
                    $typeSearch,
                    htmlspecialchars($searchClasses[ $class ]),
                    htmlspecialchars($v['place_holder']),
                    htmlspecialchars($v['display_name'])
                );

                if($v['display_flg'] == 1) {
                    $fullOptions[] = $typeOption;
                }

                $eachOption[ $class ] = $typeOption;
			}
        }

        $tagFwType = "<select class=\"top-parts-search-type\">\n";
        if(count($fullOptions) > 1) {
            array_unshift($fullOptions, sprintf("<option value=\"all\" alias_name=\"選択してください\" type_placeholder=\"%s\">%s</option>",
                    htmlspecialchars($original_search[0]['place_holder']),
                    htmlspecialchars($original_search[0]['display_name'])
                )
            );
        } else {
            $tagFwType = "<select class=\"top-parts-search-type\" style=\"display:none;\">\n";
        }
        $tagFwType.= implode("\n", $fullOptions) . "\n";
        $tagFwType.= "</select>\n";

        $tagFwTypeReidentialChintai = null;
        if(isset($eachOption[1])) {
            $tagFwTypeReidentialChintai = "<select class=\"top-parts-search-type\" style=\"display:none;\">\n"; 
            $tagFwTypeReidentialChintai.= $eachOption[1] . "\n";
            $tagFwTypeReidentialChintai.= "</select>\n";
        }
        $tagFwTypeBusinessChintai = null;
        if(isset($eachOption[2])) {
            $tagFwTypeBusinessChintai = "<select class=\"top-parts-search-type\" style=\"display:none;\">\n"; 
            $tagFwTypeBusinessChintai.= $eachOption[2] . "\n";
            $tagFwTypeBusinessChintai.= "</select>\n";
        }
        $tagFwTypeReidentialBuy = null;
        if(isset($eachOption[3])) {
            $tagFwTypeReidentialBuy = "<select class=\"top-parts-search-type\" style=\"display:none;\">\n"; 
            $tagFwTypeReidentialBuy.= $eachOption[3] . "\n";
            $tagFwTypeReidentialBuy.= "</select>\n";
        }
        $tagFwTypeBusinessBuy = null;
        if(isset($eachOption[4])) {
            $tagFwTypeBusinessBuy = "<select class=\"top-parts-search-type\" style=\"display:none;\">\n"; 
            $tagFwTypeBusinessBuy.= $eachOption[4] . "\n";
            $tagFwTypeBusinessBuy.= "</select>\n";
        }

        // 2.検索ワードフォーム(固定)
        $tagFwText = '<input placeholder="" class="freeword-top-parts-suggested" autocomplete="off" name="search_filter[fulltext_fields]" type="text" value=""/>';

        // 3.件数カウンタ―(固定)
        $tagFwCounter =<<<FWCOUNTER
<ul>
    <li class="fulltext-count-label"><label>該当件数</label></li>
    <li class="fulltext-count">
        <i>0</i>
        <i>0</i>
        <i>0</i>
        <i>0</i>
        <i>0</i>
    </li>
    <li class="fulltext-count-unit">件</li>
</ul>
FWCOUNTER;

        // 4.検索実行ボタン(固定)
        $tagFwButton = '<button class="top-parts-btn-search">検索</button>';

        // 1-4よりフォーム作成
        $tagFullForm =<<<FWFULLFORM
<form class="top_freewords_wrap">
    <h2><span>フリーワード検索</span></h2>
    $tagFwType
    $tagFwText
    $tagFwCounter
    $tagFwButton
</form>
FWFULLFORM;

        return [
            'fwFullform' => $tagFullForm,
            'fwType'     => $tagFwType,
            'fwTypeReidentialChintai' => $tagFwTypeReidentialChintai,
            'fwTypeBusinessChintai'   => $tagFwTypeBusinessChintai,
            'fwTypeReidentialBuy'     => $tagFwTypeReidentialBuy,
            'fwTypeBusinessBuy'       => $tagFwTypeBusinessBuy,
            'fwText'     => $tagFwText,
            'fwCounter'  => $tagFwCounter,
            'fwButton'   => $tagFwButton,
        ];
    }

}
 
