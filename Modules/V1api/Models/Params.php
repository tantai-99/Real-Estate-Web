<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\ClassList;
use Modules\V1api\Models\Settings;

class Params
{
    // 物件一覧の検索タイプ（s_type）
    const SEARCH_TYPE_LINE          = 1;
    const SEARCH_TYPE_CITY          = 2;
    const SEARCH_TYPE_SEIREI        = 3;
    const SEARCH_TYPE_EKI           = 4;
    const SEARCH_TYPE_MAP           = 5;
    const SEARCH_TYPE_PREF          = 6;
    const SEARCH_TYPE_CITY_POST     = 7;
    const SEARCH_TYPE_LINEEKI_POST  = 8;
    const SEARCH_TYPE_DIRECT_RESULT = 9;
    const SEARCH_TYPE_CHOSON        = 10;
    const SEARCH_TYPE_CHOSON_POST   = 11;
    const SEARCH_TYPE_FREEWORD      = 12;

    // 賃料が安い順
    const SORT_KAKAKU = 'kakaku';
    // 賃料高い順
    const SORT_KAKAKU_DESC = 'kakaku:desc';
    // 駅順
    const SORT_ENSEN_EKI = 'ensen_eki';
    const SORT_ENSEN_EKI_DESC = 'ensen_eki:desc';
    // 住所順
    const SORT_SHOZAICHI = 'shozaichi_kana';
    const SORT_SHOZAICHI_DESC = 'shozaichi_kana:desc';
    // 駅から近い順
    const SORT_EKI_KYORI = 'eki_kyori';
    const SORT_EKI_KYORI_DESC = 'eki_kyori:desc';
    // 間取り順
    const SORT_MADORI_INDEX = 'madori_index';
    const SORT_MADORI_INDEX_DESC = 'madori_index:desc';
    // 面積が広い順
    const SORT_SENYUMENSEKI = 'senyumenseki';
    const SORT_SENYUMENSEKI_DESC = 'senyumenseki:desc';
    // 築年月が浅い順
    const SORT_CHIKUNENGETSU = 'chikunengetsu';
    const SORT_CHIKUNENGETSU_DESC = 'chikunengetsu:desc';
    // 新着順
    const SORT_SHINCHAKU = 'b_muke_c_muke_er_nomi_kokai_date';
    const SORT_SHINCHAKU_DESC = 'b_muke_c_muke_er_nomi_kokai_date:desc';
    // 物件種目
    const SORT_SHUMOKU = 'bukken_shumoku';
    const SORT_SHUMOKU_DESC = 'bukken_shumoku:desc';
    // 建物面積が広い順
    const SORT_TATEMONO_MS = 'senyumenseki';
    const SORT_TATEMONO_MS_DESC = 'senyumenseki:desc';
    // 土地面積が広い順
    const SORT_TOCHI_MS = 'tochi_ms';
    const SORT_TOCHI_MS_DESC = 'tochi_ms:desc';
    // ランダム
    const SORT_RANDAM = 'random';
    const PIC_BUKKEN = 1;
    const PIC_MADORI = 2;

    const SORT_CMS_MANAGEMENT_NO = 'jisha_kanri_no:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_MANAGEMENT_NO_DESC = 'jisha_kanri_no:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_HOUSE_NO = 'bukken_no:asc';
    const SORT_CMS_HOUSE_NO_DESC = 'bukken_no:desc';
    const SORT_CMS_SHUMOKU = 'bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_SHUMOKU_DESC = 'bukken_shumoku:desc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_KAKAKU = 'kakaku:asc,bukken_shumoku:asc,eki_kyori:asc';
    const SORT_CMS_KAKAKU_DESC = 'kakaku:desc,bukken_shumoku:asc,eki_kyori:asc';
    const SORT_CMS_SHINCHAKU = 'b_muke_c_muke_er_nomi_kokai_date:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_SHINCHAKU_DESC = 'b_muke_c_muke_er_nomi_kokai_date:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_SHOZAICHI = 'shozaichi_kana:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_SHOZAICHI_DESC = 'shozaichi_kana:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_ENSEN_EKI = 'ensen_eki:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_ENSEN_EKI_DESC = 'ensen_eki:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_HOUSE_CATEGORY = 'jishatasha_cd:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_HOUSE_CATEGORY_DESC = 'jishatasha_cd:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_RECOMMENDED = 'ippan_kokai_message_umu:asc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    const SORT_CMS_RECOMMENDED_DESC = 'ippan_kokai_message_umu:desc,bukken_shumoku:asc,kakaku:asc,eki_kyori:asc';
    
    // ATHOME_HP_DEV-5453 物件IDの桁数
    const BUKKEN_ID_DIGIT = 24;

    private $paramas;

    public function __construct($params)
    {
        if (is_null($params))
        {
            throw new \Exception('$params is null.');
        }
        $paramsObject = new \stdClass();
        foreach ($params as $param => $value) {
            $paramsObject->{$param} = $value;
        }
        $this->paramas = $paramsObject;
    }

    public function getComId()
    {
        return $this->paramas->com_id;
    }

    public function isCmsPublish() {
        if (\App::environment() == 'production') {
            return false;
        }
        return $this->paramas->publish == '_cms';
    }

    public function isProdPublish()
    {
       return $this->paramas->publish == '1';
    }

    public function isTestPublish()
    {
        return $this->paramas->publish == '2';
    }

    public function isAgencyPublish()
    {
        return $this->paramas->publish == '3';
    }

    public function isCmsSpecial() 
    {
        return isset($this->paramas->cms_special) ? $this->paramas->cms_special : false;
    }

    public function isPcMedia()
    {
        return (! isset($this->paramas->media)) || $this->paramas->media == 'pc';
    }

    public function isSpMedia()
    {
        return $this->paramas->media == 'sp';
    }

    // ATHOME_HP_DEV-5001
    public function isFromCms()
    {
        if(isset($this->paramas->fromcms) && $this->paramas->fromcms == true) {
            return true;
        }
        return false;
    }

    public function getTypeCt()
    {
        $result = null;
        if (isset($this->paramas->f_type)) {
            $class = TypeList::getInstance()->getClassByUrlComopsite($this->paramas->f_type);
            if ($class) {
                $settings = new Settings($this);
                $estateType = [];
                if($class === ClassList::CLASS_ALL){
                    $estateSettngRows = $settings->company->getHpEstateSettingRow()->getSearchSettingAll();
                    foreach($estateSettngRows as $estateSettngRow){
                        $enabledEstateType = explode(',', $estateSettngRow->enabled_estate_type);
                        $estateType = array_merge($estateType,$enabledEstateType); 
                    }
                }else{
                    $estateSettngRow = $settings->company->getHpEstateSettingRow()->getSearchSetting($class);
                    $estateType = explode(',', $estateSettngRow->enabled_estate_type);
                }
                foreach ($estateType as $type) {
                    $result[] = TypeList::getInstance()->getUrl($type);
                }
                $result = count($result) == 1 ? $result[0] : $result;

                return $result;
            }
        }
        if (isset($this->paramas->type_ct)) {
            if (!empty($this->paramas->type_ct)) {
                if (is_array($this->paramas->type_ct)) {
                    $type_ct = $this->paramas->type_ct;
                    
                } else {
                    $type_ct = explode(',', $this->paramas->type_ct);
                }
                return count($type_ct) == 1 ? $this->paramas->type_ct : $type_ct;
            }
        }
        
        return null;
    }

    public function getKenCt()
    {
        return isset($this->paramas->ken_ct)?
                $this->paramas->ken_ct:
                null;
    }

    public function getShikugunCt()
    {
        $result = null;
        if (isset($this->paramas->shikugun_ct))
        {
            $result = explode(',', $this->paramas->shikugun_ct);
            $result = count($result) == 1 ? $result[0] : $result;
        }
        return  $result;
    }

    public function getChosonCt() {
        $result = [];
        if (isset($this->paramas->choson_ct) && $this->paramas->choson_ct)
        {
            $result = explode(',', $this->paramas->choson_ct);
        }
        return  $result;
    }

    public function getEnsenCt()
    {
        $result = null;
        if (isset($this->paramas->ensen_ct))
        {
            $result = explode(',', $this->paramas->ensen_ct);
            $result = count($result) == 1 ? $result[0] : $result;
        }
        return  $result;
    }

    public function getEkiCt()
    {
        $result = null;
        if (isset($this->paramas->eki_ct))
        {
            $result = explode(',', $this->paramas->eki_ct);
            $result = count($result) == 1 ? $result[0] : $result;
        }
        return  $result;
    }

    public function getLocateCt()
    {
        return isset($this->paramas->locate_ct)?
                $this->paramas->locate_ct:
                null;
    }

    public function getSpecialPath() {
        return isset($this->paramas->special_path)?
                $this->paramas->special_path:
                null;
    }

    public function getSpecialId() {
        return isset($this->paramas->special_id)?
                $this->paramas->special_id:
                null;
    }

    public function getAction() {
        return isset($this->paramas->action)?
            $this->paramas->action:
            null;
    }

    public function getPage($default = null) {
        return !empty($this->paramas->page)&&is_numeric($this->paramas->page)&&$this->paramas->page>0?
                $this->paramas->page:
                $default;
    }

    public function getPerPage($default = 30) {
        return !empty($this->paramas->per_page)&&is_numeric($this->paramas->per_page)&&$this->paramas->per_page>0?
                $this->paramas->per_page:
                $default;
    }

    public function getFulltext($default = null) {
        return !empty($this->paramas->fulltext) ? $this->paramas->fulltext: $default;
    }

    public function getSort($sType = null,$ctType = null)
    {
        $result = '';
        if(isset($this->paramas->sort)){
            return $this->paramas->sort;
        }else if(!is_null($sType)){
            switch($sType){
                //検索タイプでデフォルト値が違う
                case self::SEARCH_TYPE_LINE:
                case self::SEARCH_TYPE_EKI:
                case self::SEARCH_TYPE_LINEEKI_POST:
                    $sortType = 'ensen_eki,';
                    break;
    
                case self::SEARCH_TYPE_CITY:
                case self::SEARCH_TYPE_SEIREI:
                case self::SEARCH_TYPE_PREF:
                case self::SEARCH_TYPE_CITY_POST:
                case self::SEARCH_TYPE_CHOSON:
                case self::SEARCH_TYPE_CHOSON_POST:
                    $sortType = 'shozaichi_kana,';
                    break;
                case self::SEARCH_TYPE_FREEWORD:
                    $sortType = '';
                    break;
            }
            $result = 'kakaku,'.$sortType.'eki_kyori';
            if($ctType === 'kodate'){
                //戸建てのときだけデフォルト値が違う
                $result = 'joi_shumoku:desc,'.$result; 
            }
            return $result;
        }
        return self::SORT_KAKAKU;
    }
    public function getSortCMS()
    {
        if(isset($this->paramas->sort_cms)){
            return $this->paramas->sort_cms;
        }
        return Params::SORT_CMS_SHINCHAKU_DESC;
    }

    public function getSearchType()
    {
        return isset($this->paramas->s_type) && is_numeric($this->paramas->s_type)?
                $this->paramas->s_type:
                null;
    }

    public function getBukkenId()
    {
        $result = null;
        if (isset($this->paramas->bukken_id) && !empty($this->paramas->bukken_id))
        {
            $result = explode(',', $this->paramas->bukken_id);
            // ATHOME_HP_DEV-5453 物件IDの桁数・数値チェックを行う
            foreach ($result as $bukkenId) {
                if (strlen($bukkenId) != self::BUKKEN_ID_DIGIT && strpos($bukkenId, ':') !== false) {
                    $bukkenId = substr($bukkenId, 0, strcspn($bukkenId,':'));
                }
                if (strlen($bukkenId) != self::BUKKEN_ID_DIGIT || !ctype_xdigit($bukkenId)) {
                    throw new \Exception('物件IDの値が不正です（値：' . $bukkenId . ')', 404);
                }
            }
            $result = count($result) == 1 ? $result[0] : $result;
        }
        return  $result;
    }

    public function getHistory()
    {
        $result = null;
        if (isset($this->paramas->history) && !empty($this->paramas->history))
        {
            $result = explode(',', $this->paramas->history);
            $result = count($result) == 1 ? $result[0] : $result;
        }
        return  $result;
    }

    public function getTab()
    {
        return isset($this->paramas->tab) && is_numeric($this->paramas->tab)?
                $this->paramas->tab:
                null;
    }

    public function getPanorama()
    {
        return isset($this->paramas->panorama) && is_numeric($this->paramas->panorama)?
            $this->paramas->panorama:
            null;
    }

    public function getMTypeCt()
    {
        return isset($this->paramas->m_type_ct)?
                $this->paramas->m_type_ct:
                null;
    }

    public function getKomaSort()
    {
        return isset($this->paramas->sort_option) && is_numeric($this->paramas->sort_option)?
                $this->paramas->sort_option:
                null;
    }

    public function getKomaRows()
    {
        return isset($this->paramas->rows)?
                $this->paramas->rows:
                null;
    }

    public function isPicBukken()
    {
        return isset($this->paramas->pic) && is_numeric($this->paramas->pic) ?
                $this->paramas->pic == $this::PIC_BUKKEN:
                true;
    }

    // public function isCountOnly()
    // {
    //     return isset($this->paramas->countOnly) ?
    //             $this->paramas->countOnly:
    //             false;
    // }

    public function getSearchFilter() {
        return isset($this->paramas->search_filter)&&is_array($this->paramas->search_filter)?
            $this->paramas->search_filter:
            [];
    }

    public function setParam($name, $value) {
        $this->paramas->{$name} = $value;
    }

    public function getParam($name) {
        if(isset($this->paramas->{$name})) {
            return $this->paramas->{$name};
        }
        return null;
    }

    public function getDirectAccess() {

        return isset($this->paramas->direct_access) && $this->paramas->direct_access === '1';
    }

    public function getMcityCt() {

        return isset($this->paramas->locate_ct) ? $this->paramas->locate_ct : null;
    }

    public function getFromRecommend() {
        return isset($this->paramas->from_recommend) ? $this->paramas->from_recommend : null;
    }

    public function getApiKey() {
        return isset($this->paramas->api_key) ? $this->paramas->api_key : null;
    }

    public function getContactCt()
    {
        return isset($this->paramas->contact_type)?
            $this->paramas->contact_type:
            null;
    }

    public function getAllowRedirect()
    {
        return isset($this->paramas->allow_redirect)?
            $this->paramas->allow_redirect:
            null;
    }

    public function getIdoKeidoNansei()
    {
        return isset($this->paramas->sw_lat_lan)?
            $this->paramas->sw_lat_lan:
            null;
    }

    public function getIdoKeidoHokuto()
    {
        return isset($this->paramas->ne_lat_lan)?
            $this->paramas->ne_lat_lan:
            null;
    }

    public function getCenter()
    {
        return isset($this->paramas->center)?
                $this->paramas->center:
                null;
    }

    public function getUserIp()
    {
        return isset($this->paramas->user_ip)?
                $this->paramas->user_ip:
                null;
    }

    public function getDisableSTypeTab()
    {
        return isset($this->paramas->disable_s_type_tab)?
                $this->paramas->disable_s_type_tab:
                null;
    }

    public function getFromSearchmap()
    {
        return isset($this->paramas->from_searchmap)?
                1:
                0;
    }

    public function isOnlyChosonModal() {
        return isset($this->paramas->only_choson_modal) && !!$this->paramas->only_choson_modal;
    }

    public function getKomaColumns() {
        return isset($this->paramas->columns) ? $this->paramas->columns : null;
    }

    public function isFreeword() {
        return $this->getSearchType() == self::SEARCH_TYPE_FREEWORD;
    }
    public function getFType () {
        return isset($this->paramas->f_type)?
                $this->paramas->f_type:
                null;
    }

    // 4293 Add log detail FDP
    public function getRefere() {
        return isset($this->paramas->referer) ? $this->paramas->referer : null;
    }

    // 4835
    public function getBukkenNo() {
        return isset($this->paramas->bukken_no) ? $this->paramas->bukken_no : null;
    }
    public function getSetting() {
        return isset($this->paramas->setting) ? json_decode($this->paramas->setting, true) : null;
    }

    public function getIsCount() {
        return isset($this->paramas->is_count) ? $this->paramas->is_count : null;
    }
    public function getIsModal() {
        return isset($this->paramas->is_modal) ? $this->paramas->is_modal : null;
    }

    public function getIsConfirm() {
        return isset($this->paramas->is_confirm) ? $this->paramas->is_confirm : null;
    }

    public function getIsCondition() {
        return isset($this->paramas->is_condition) ? $this->paramas->is_condition : null;
    }

    public function getLinkPage() {
        return isset($this->paramas->link_page) ? $this->paramas->link_page : null;
    }

    public function getIsTitle() {
        return isset($this->paramas->is_title) ? $this->paramas->is_title : null;
    }

    public function getOperation() {
        return isset($this->paramas->operation) ? $this->paramas->operation : null;
    }

    public function getUserId() {
        return isset($this->paramas->user_id) ? $this->paramas->user_id : null;
    }

    public function getSpSession() {
        return isset($this->paramas->sp_session) ? $this->paramas->sp_session : false;
    }
}