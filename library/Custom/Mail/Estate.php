<?php
namespace Library\Custom\Mail;

use Modules\V1api\Services;
/**
 *  物件お問い合わせ返信メール用ラベル
 */
class Estate extends MailAbstract
{
    const ESTATE_CLASS                    = 200;  // 物件種目
    const TATEMONO_NAME                   = 201;  // 建物名
    const KOUTSU_ROSEN                    = 202;  // 交通
    const STATION_NAME                    = 203;  // 駅名
    const STATION_TOHO_FUN                = 204;  // 徒歩
    const BUSTEI_NAME                     = 205;  // バス停名
    const BUS_JIKAN                       = 206;  // バス乗車分
    const BUSTEI_JIKAN                    = 207;  // バス停歩分
    const PROPERTY_LOCATION               = 208;  // 所在地
    const PROPERTY_RENT                   = 209;  // 賃料
    const PROPERTY_KAKAKU                 = 210;  // 賃料
    const PROPERTY_LAYOUT                 = 211;  // 間取り
    const PROPERTY_EXCLUSIVE_AREA         = 212;  // 専有面積
    const PROPERTY_USING_AREA             = 213;  // 使用部分面積
    const PROPERTY_LAND_AREA              = 214;  // 土地面積
    const BUKKEN_NO                       = 215;
    const KANRI_NO                        = 216;
    
    public function __construct(){
        
        parent::__construct();

    }

    //ラベルを定義
    private $itemCodeLabel = array(
        self::ESTATE_CLASS                => '物件種目',
        self::TATEMONO_NAME               => '建物名',
        self::KOUTSU_ROSEN                => '交通',
        self::STATION_NAME                => '駅名',
        self::STATION_TOHO_FUN            => '徒歩',
        self::BUSTEI_NAME                 => 'バス停名',
        self::BUS_JIKAN                   => 'バス乗車分',
        self::BUSTEI_JIKAN                => 'バス停歩分',
        self::PROPERTY_LOCATION           => '所在地',
        self::PROPERTY_RENT               => '賃料',
        self::PROPERTY_KAKAKU             => '価格',
        self::PROPERTY_LAYOUT             => '間取り',
        self::PROPERTY_EXCLUSIVE_AREA     => '専有面積',
        self::PROPERTY_USING_AREA         => '使用部分面積',
        self::PROPERTY_LAND_AREA          => '土地面積',
        self::BUKKEN_NO                   => 'アットホーム物件番号',
        self::KANRI_NO                    => '貴社物件管理番号',
    );


    public function getItemLabels() {
        return $this->itemCodeLabel;
    }

    public function getItemCodesMap($isUriJigyoPackage) {

        //データとのマッピング
        $itemCodeMap = array(
            'shumoku_nm'          => self::ESTATE_CLASS             ,//物件種目           
            'csite_bukken_title'  => self::TATEMONO_NAME            ,//建物名
            'ensen_nm'            => self::KOUTSU_ROSEN             ,//交通
            'eki_nm'              => self::STATION_NAME             ,//駅名
            'toho'                => self::STATION_TOHO_FUN         ,//徒歩
            'csite_shozaichi'     => self::PROPERTY_LOCATION        ,//所在地
            'kakaku'              => self::PROPERTY_RENT            ,//賃料
            'madori'              => self::PROPERTY_LAYOUT          ,//間取り
            'tochi_ms'            => self::PROPERTY_LAND_AREA       ,//土地面積
            'bukken_no'           => self::BUKKEN_NO                ,//at home物件番号
            'kanri_no'            => self::KANRI_NO                 ,//貴社物件管理番号
        );

        if ($this->_category == "livingbuy" || $this->_category == "officebuy" ) {
            $itemCodeMap['kakaku'] = self::PROPERTY_KAKAKU;//価格
        }
        if ($isUriJigyoPackage) {
            $itemCodeMap['tatemono_nobe_ms'] = self::PROPERTY_EXCLUSIVE_AREA;//専有面積
        } else {
            $itemCodeMap['tatemono_ms'] = self::PROPERTY_EXCLUSIVE_AREA;//専有面積
            if ($this->_category == "officelease"){
                $itemCodeMap['tatemono_ms'] = self::PROPERTY_USING_AREA;//使用部分面積
            }
        }
        return $itemCodeMap;
    }
    protected function geLabels(){

        $dstLabels = parent::geLabels();

        // 物件お問い合わせ項目のラベルを取得する
        $estateContactLabels = $this->getItemLabels();
        foreach( $estateContactLabels as $key=>$val ){
            $dstLabels[$key] =  parent::labelPadding($estateContactLabels[$key]);
        }
        return $dstLabels;
    }

    protected function createMailParams($contactItems){

        // メール用に問い合わせ項目を作成
        $inquiryParams = array();
        $content = array();
        $profile = array();
        $order = array();
        $bukken = array();
        $bukken_no = array();
        $url = array();

        foreach( $contactItems['contactItems'] as $key=>$items ){
            // お問い合わせアイテムコードを取得する
            $itemCode = $this->_contactForm->getItemCode($items['item_key']);
            if ($itemCode==null){
                continue;
            }

            // お問い合わせ内容
            if ( in_array($itemCode, $this->contentCode) ){
                if(array_key_exists('value',$items)){
                    $content = $items['value'];
                }
                if(array_key_exists('subject_more_item_value',$items)){
                    $content['remarks'] = $items['subject_more_item_value'];
                }

            // プロフィール
            } else if( in_array($itemCode, $this->profileCode) ) {
                if(array_key_exists('value',$items)){
                    $profile[$itemCode] = $this->getPofileItemText($items);
                }

            // 依頼内容
            } else if( in_array($itemCode, $this->orderCode) ) {
                if(array_key_exists('value',$items)){
                    $order[$itemCode] = $this->getPofileItemText($items);
                }
            }
        }

        //物件お問い合わせ
        $displayModel = $contactItems['estateData']['bukken']['display_model'];
        $dataModel = $contactItems['estateData']['bukken']['data_model'];
        // 物件の上位種目が「売事業用（一括）(05)」かどうか
        $isUriJigyoPackage = Services\ServiceUtils::getVal('joi_shumoku_cd', $displayModel) == '05';
        $bukkenItemCode = $this->getItemCodesMap($isUriJigyoPackage);

        if(isset($contactItems['estateData']['domain']) && isset($contactItems['estateData']['shumoku'])) {
            $estate_id = $contactItems['estateData']['estate-id'];
            $url = 'https://'.$contactItems['estateData']['domain'].'/'.$contactItems['estateData']['shumoku'].'/detail-'.$estate_id;
        }

        foreach ($this->bukkenCode as $key) {
            $bukken[$key] = '';
        }

        foreach ($displayModel as $key => $value) {
            if( array_key_exists($key, $bukkenItemCode) && in_array($bukkenItemCode[$key], $this->bukkenCode)) {
                $bukken[$bukkenItemCode[$key]] = $value;
            }
            if($key == 'kotsus') {
                $bukken[$bukkenItemCode['ensen_nm']] = array_key_exists('ensen_nm', $value[0]) ? $value[0]['ensen_nm'] : "";
                $bukken[$bukkenItemCode['eki_nm']]   = array_key_exists('eki_nm', $value[0])   ? $value[0]['eki_nm']   : "";
                $bukken[$bukkenItemCode['toho']]     = array_key_exists('toho', $value[0])     ? $value[0]['toho']     : "";
                //$bukken[$bukkenItemCode['bus_to']]   = array_key_exists('bus_to', $value[0])     ? $value[0]['bus_to']     : "";
            }
            if($key == 'bukken_no' || $key == 'kanri_no') {
                $bukken_no[$bukkenItemCode[$key]] = $value;
            }
        }

        // バス情報
        $bukken[self::BUSTEI_NAME]  = "";
        $bukken[self::BUS_JIKAN]    = "";
        $bukken[self::BUSTEI_JIKAN] = "";
        if(isset($dataModel['kotsus']) && isset($dataModel['kotsus'][0])) {
            $kotsu = $dataModel['kotsus'][0];
            if(isset($kotsu['bustei_nm'])){
                $bukken[self::BUSTEI_NAME]    = $kotsu['bustei_nm'];
            }
            if(isset($kotsu['bus_jikan'])){
                $bukken[self::BUS_JIKAN]      = $kotsu['bus_jikan']."分";
            }
            if(isset($kotsu['bustei_jikan'])){
                $bukken[self::BUSTEI_JIKAN]   = $kotsu['bustei_jikan']."分";
            }
        }

        $inquiryParams['content'] = $content;
        $inquiryParams['profile'] = $profile;
        $inquiryParams['order']   = $order;
        $inquiryParams['bukken']  = $bukken;
        $inquiryParams['bukken_no']  = $bukken_no;
        $inquiryParams['url']  = $url;

        //2次広告自動公開の元付会員情報
        if ($contactItems['estateData']['second_estate_flg']){
            $secondEstateKaiin = $contactItems['estateData']['secondEstate'];
            $motoKaiin['motokai_name'] = isset($secondEstateKaiin->seikiShogo['shogoName']) ? $secondEstateKaiin->seikiShogo['shogoName'] : "";
            $motoKaiin['motokai_tel']  = isset($secondEstateKaiin->contact['daihyoTel'])    ? $secondEstateKaiin->contact['daihyoTel']    : "";
            $motoKaiin['motokai_mail'] = isset($secondEstateKaiin->contact['daihyoMail'])   ? $secondEstateKaiin->contact['daihyoMail']   : "";
            $inquiryParams['second_estate'] = $motoKaiin;
        }

        // #4294
        if (isset($contactItems['peripheral_flg']) && ($contactItems['peripheral_flg'] == 1)){
            $inquiryParams['peripheral_flg']  = $contactItems['peripheral_flg'];
        }
		// END #4294
        return $inquiryParams;
    }

    public function setInquiryParams($contactItems){

        $params = $this->createMailParams($contactItems);
        unset($contactItems['peripheral_flg']);

        // お問い合わせ項目のラベルを取得する
        $contactLabels = $this->geLabels();
        
        // パラメータにラベルを追加する
        $labels = array();
        foreach($params['profile'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  ($labels[$key]);
            }
        }
        foreach($params['order'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }

        foreach($params['bukken'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }

        foreach($params['bukken_no'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }

        $keyParams = array_keys($params);
        foreach ($labels as $key => $value) {
            foreach ($keyParams as $valKeyParams) {
                if ($valKeyParams == 'url' || $valKeyParams == 'content' || $valKeyParams == 'peripheral_flg') {
                    continue;
                }
                if (isset($params[$valKeyParams][$key]))
                {
                    if ($params[$valKeyParams][$key]) {
                        $params[$valKeyParams][$key] = $this->convertContentExceptLabel($value, $params[$valKeyParams][$key]);
                    }
                }
            }
        }

        foreach($params['content'] as $key=>&$val){
            $val = $this->convertContent($val);
        }

        $params['label'] = $labels;
        parent::setInquiryParams($params);

        return;
    }

   public function setInquiryParamsCrm(array $contactItems){

        $params = $this->createMailParams($contactItems);

        // プロフィールを作る
        $dstProfile = array();
        foreach( $this->profileCode as $key => $val ){
            $dstProfile[$val] = "";
            if( array_key_exists( $val, $params['profile'] ) ){
                $dstProfile[$val] = $params['profile'][$val];
            }
        }
        $params['profile'] = $dstProfile;


        // 依頼内容を作る
        $dstOrder = array();
        foreach( $this->orderCode as $key => $val ){
            $dstOrder[$val] = "";
            if( array_key_exists( $val, $params['order'] ) ){
                $dstOrder[$val] = $params['order'][$val];
            }
        }
        $params['order'] = $dstOrder;

        // お問い合わせ項目のラベルを取得する
        $contactLabels = $this->geLabels();
        
        // パラメータにラベルを追加する
        $labels = array();
        foreach($params['profile'] as $key=>$val){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
        }
        foreach($params['order'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }
        foreach($params['bukken'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }
        foreach($params['bukken_no'] as $key=>$val){
            if (array_key_exists($key,$contactLabels)){
                $freeLabel = $this->getFreeLabel($contactItems,$key);
                $labels[$key] =  (is_null($freeLabel)) ? $contactLabels[$key] : $freeLabel ;
                $labels[$key] =  $this->convertLabel($labels[$key]);
            }
        }

        $keyParams = array_keys($params);
        foreach ($labels as $key => $value) {
            foreach ($keyParams as $valKeyParams) {
                if (isset($params[$valKeyParams][$key]))
                {
                    $params[$valKeyParams][$key] = $this->convertContentExceptLabel($value, $params[$valKeyParams][$key]);
                }
            }
        }

        $params['label'] = $labels;

        parent::setInquiryParams($params);
        return;
    }

}