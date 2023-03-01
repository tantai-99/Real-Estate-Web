<?php

require_once(APPLICATION_PATH.'/../script/Validate.php');
require_once(APPLICATION_PATH.'/../script/Maxlength.php');

/*
 * お問い合わせ画面
 *
 */
define('APOSTROPHE_MARKS', "'");
define('QUOTATION_MARKS', '"');
class Contact {

    public function __construct($context) {

        $this->_context = $context;
        $this->validate = new Validate();
    }

    public function init($contactName) {

        $this->_contactName  = $contactName;
        $iniFile             = glob($this->_context->settingPath."/contact_".$contactName."_*.ini")[0];
        $this->_contactIni   = parse_ini_file($iniFile, true);
        $this->_auth         = $this->_contactIni['auth'];
        $this->_base         = $this->_contactIni['base'];
        $this->_inquiry_mail = $this->_contactIni['inquiry_mail'];
        $this->_reply_mail   = $this->_contactIni['reply_mail'];

        $this->_contactItems = $this->getContactItem();
        $this->_errors       = [];
        $this->view          = new stdClass;
        $this->view->base    = $this->_base;
        $this->ua            = new UserAgent();
    }

    public function runEdit() {
        if (isset($_POST['back'])) {
            $params              = $_POST;
            $this->_contactItems = $this->populate($params);
            $isSuccess           = $this->validate($this->_contactItems, $params);
            if ($isSuccess) {
                $this->view->contactItems = $this->_contactItems;
                return;
            }
        }
        $this->view->errors       = $this->_errors;
        $this->view->contactItems = $this->_contactItems;
    }

    public function validateByAjax() {
        $params              = $_POST;
        $this->_contactItems = $this->populate($params);
        $isSuccess           = $this->validate($this->_contactItems, $params);
        $data = [];
        $data['errorMsg'] = $this->_errors;
        $output = [
            "isValid" => $isSuccess ,
            "data" => $data,
        ];
        $output = json_encode($output);
        header( 'Content-Type: text/javascript; charset=utf-8' );
        echo $output;
        exit;
    }

    public function runConfirm() {
        $params              = $_POST;
        $this->_contactItems = $this->populate($params);
        if (isset($_POST['next'])) {
            $this->view->contactItems = $this->_contactItems;
            return;
        }
        if (isset($_POST['send'])) {
            // メールを送信する
            $clickDate = $_POST['click_date'] ? $_POST['click_date'] : null;
            $result = $this->apiSend($this->_contactItems, $clickDate);
            if ($result == true) {
                // 確認画面にリダイレクト
                header('location:'.'/'.$this->_base['filename'].'/complete/');
                exit;
            }
        }
        // 編集画面にリダイレクト
        header('location:'.'/'.$this->_base['filename'].'/edit/');
    }

    public function getView() {

        return $this->view;
    }

    /**
     * @param string $itemKey
     * @return string|null
     */
    public static function getItemUnitWord($itemKey) {
        $units = array(
            'person_age'               => '歳',
            'property_age'             => '年',
            'person_number_of_family'  => '人',
            'person_annual_incom'      => '万円',
            'person_own_fund'          => '万円',
            'property_budget'          => '万円',
            'property_number_of_house' => '戸'
        );
        return array_key_exists($itemKey, $units) ? $units[$itemKey] : null;
    }

    private function getContactItem() {

        $contactItems = [];
        // iniファイルを見る
        foreach ($this->_contactIni as $key => $val) {
            // アイテム（item_*）だけを対象とする
            if ((strncmp($key, 'item_', 5) != 0) || (!array_key_exists('use_flg', $val) || !$val['use_flg'])) {
                continue;
            }

            if (array_key_exists($val['item_key'], Maxlength::$contactMap)) { 
                $val['maxlength'] = Maxlength::$contactMap[$val['item_key']];
            } else if ($val['item_key'] == 'person_tel') {
                $val['maxlength'] = array(
                    Maxlength::$contactMap['person_tel1'],
                    Maxlength::$contactMap['person_tel2'],
                    Maxlength::$contactMap['person_tel3']
                );
            }
            switch ($val['item_key']) {
                case 'person_mail':
                    $val['validatelength'] = 62;
                    break;
                case 'request':
                    $val['validatelength'] = 1000;
                    break;
                default:
                    $val['validatelength'] = $val['maxlength'];
                    break;
            }

            // チェックボックス
            if ($val['type'] == 'checkbox' || $val['type'] == 'radio') {
                $val['option_checked'] = [];
            }
            // ドロップダウン
            else if ($val['type'] == 'select') {
                $val['option_selected'] = "";
            }
            // テキスト・テキストエリア
            else if ($val['type'] == 'text' || $val['type'] == 'textarea') {
                $val['item_value'] = "";
            }

            // お問い合わせ内容の項目に備考を追加
            if ($val['item_key'] == 'subject') {
                $val['subject_more_item_key']   = $val['sub_item_key'];
                $val['subject_more_item_value'] = "";
            }
            // 物件リクエストの項目に備考を追加
            elseif ($val['item_key'] == 'request') {
                $val['request_more_item_key']   = $val['sub_item_key'];
                $val['request_more_item_value'] = "";
            }
            else if ($val['item_key'] == 'property_layout') {
                $val['sub_option_selected'] = [];
            }
            else if ($val['item_key'] == 'property_exclusive_area' || $val['item_key'] == 'property_building_area' || $val['item_key'] == 'property_land_area' || $val['item_key'] == 'property_age') {
                $val['sub_option_checked'] = [];
            }

            $contactItems[] = $val;
        }

        // 連絡先をまとめる
        $connections = [];
        $dstKeys     = [];
        foreach ($contactItems as $key => $val) {
            if ($val['item_key'] != 'person_mail' && $val['item_key'] != 'person_tel' && $val['item_key'] != 'person_other_connection') {
                continue;
            }
            $connections[$val['item_key']] = $val;
            $dstKeys[]                     = $key;
        }
        if (count($connections) >= 1) {
            foreach ($dstKeys as $item_key) {
                // 最初の連絡先アイテムにまとめる
                if ($item_key === reset($dstKeys)) {
                    $contactItems[$item_key]['item_key']   = 'connection';
                    $contactItems[$item_key]['type']       = 'connection';
                    $contactItems[$item_key]['label']      = '連絡先';
                    $contactItems[$item_key]['use_flg']    = true;
                    $contactItems[$item_key]['must_flg']   = true;
                    $contactItems[$item_key]['annotation'] = $this->_contactIni['item_contact_info']['contactInfoAnnotation'];
                    $contactItems[$item_key]['items']      = $connections;
                }
                else {
                    unset($contactItems[$item_key]);
                }
            }
        }
        return $contactItems;
    }

    private function populate($params) {

        $contactItems      = [];
        $personTelFirst = $params['person_tel1'];
        $personTelSecond = $params['person_tel2'];
        $personTelThird = $params['person_tel3'];
        // 電話番号をまとめる
        if (!empty($personTelFirst) && !empty($personTelSecond) && !empty($personTelThird)) {
            $personTelValue = $personTelFirst . '-' . $personTelSecond . '-' . $personTelThird;
        }
        foreach ($this->_contactItems as $key => $val) {

            // 連絡先
            if ($val['type'] == 'connection') {
                $connectionItems = [];
                foreach ($val['items'] as $connectionItem) {
                    $connectionItem['item_value'] = '';
                    if (array_key_exists($connectionItem['item_key'], $params)) {
                        $connectionItem['item_value'] = $params[$connectionItem['item_key']];
                    }
                    // 電話番号をセットする
                    if ($connectionItem['item_key'] === 'person_tel' && isset($personTelValue)) {
                        $connectionItem['item_value_1'] = $personTelFirst;
                        $connectionItem['item_value_2'] = $personTelSecond;
                        $connectionItem['item_value_3'] = $personTelThird;
                        $connectionItem['item_value'] = $personTelValue;
                    }
                    $connectionItems[] = $connectionItem;
                }
                $val['items'] = $connectionItems;
            }
            // チェックボックス
            else if ($val['type'] == 'checkbox' || $val['type'] == 'radio') {
                if (array_key_exists($val['item_key'], $params)) {
                    if (!is_null($params[$val['item_key']])) {
                        $val['option_checked'] = $params[$val['item_key']];
                    }
                }
            }
            // プルダウン
            else if ($val['type'] == 'select') {
                if (array_key_exists($val['item_key'], $params)) {
                    $val['option_selected'] = $params[$val['item_key']];
                }
            }
            // テキスト・テキストエリア
            else if ($val['type'] == 'text' || $val['type'] == 'textarea') {
                if (array_key_exists($val['item_key'], $params)) {
                    $val['item_value'] = $params[$val['item_key']];
                }
            }

            // お問い合わせ内容の項目に備考を追加
            if ($val['item_key'] == 'subject') {
                $val['subject_more_item_value'] = $params[$val['subject_more_item_key']];
            }
            // 物件リクエストの項目に備考を追加
            elseif ($val['item_key'] == 'request') {
                $val['request_more_item_value'] = $params[$val['request_more_item_key']];
            }
            else if ($val['item_key'] == 'property_layout') {
                $val['sub_option_selected'] = $params[$val['item_key'].'_sub'];
            }
            else if ($val['item_key'] == 'property_exclusive_area' || $val['item_key'] == 'property_building_area' || $val['item_key'] == 'property_land_area' || $val['item_key'] == 'property_age') {
                $val['sub_option_checked'] = (is_null($params[$val['item_key'].'_sub'])) ? [] : $params[$val['item_key'].'_sub'];
            }
            $contactItems[] = $val;
        }
        return $contactItems;
    }

    /* 
     * 
     */
    private function validate($contactItems, $params) {

        $this->_errors = [];

        $personTelItems = array_filter(array(
            $params['person_tel1'],
            $params['person_tel2'],
            $params['person_tel3']
        ));
        foreach ($contactItems as $item) {
            // 必須
            if ($item['must_flg']) {
                if ($item['type'] == 'connection') {
                    $isInput     = true;
                    $isInputTel  = false;
                    $isInputMail = false;
                    $params['person_tel'] = '';
                    foreach ($item['items'] as $connectionItem) {
                        if ($connectionItem['item_no'] == 11 && !empty($connectionItem['item_value'])) {
                            $isInputMail = true;
                        }
                        if ($connectionItem['item_no'] == 12) {
                            $connectionItem['item_value'] = implode('-', $personTelItems);
                            if (!empty($connectionItem['item_value'])) {
                                $isInputTel = true;
                                $params['person_tel'] = $connectionItem['item_value'];
                            }
                        }
                        if ($connectionItem['must_flg'] && empty($connectionItem['item_value'])) {
                            $isInput = false;
                        }
                    }
                    // 必須チェック
                    if((!$isInput) || (!$isInputTel && !$isInputMail)) {
                        $this->_errors[$item['item_key']][] = $this->errorMsgInput($item['label']);
                    }
                }
                else if ($item['type'] == 'checkbox' || $item['type'] == 'radio') {
                    if (empty($item['option_checked'])) {
                        $this->_errors[$item['item_key']][] = $this->errorMsgSelect($item['label']);
                    }
                }
                else if ($item['type'] == 'select') {
                    if ($item['option_selected'] == 1) {
                        $this->_errors[$item['item_key']][] = $this->errorMsgSelect($item['label']);
                    }
                    else if ($item['item_key'] == 'property_layout') {
                        if ($item['sub_option_selected'] == 1) {
                            $this->_errors[$item['item_key']."_sub"][] = $this->errorMsgInput($item['label']);
                        }
                    }
                }
                else if ($item['type'] == 'text' || $item['type'] == 'textarea') {
                    if (empty($item['item_value'])) {
                        $this->_errors[$item['item_key']][] = $this->errorMsgInput($item['label']);
                    }
                    else if ($item['item_key'] == 'property_exclusive_area' || $item['item_key'] == 'property_building_area' || $item['item_key'] == 'property_land_area' || $item['item_key'] == 'property_age') {
                        if (empty($item['sub_option_checked'])) {
                            $this->_errors[$item['item_key']."_sub"][] = $this->errorMsgInput($item['label']);
                        }
                    }
                }
            }

            if ($item['type'] == 'checkbox' || $item['type'] == 'radio') {
                foreach ($item['option_checked'] as $checked) {
                    if ($checked <= 0 || $checked > count($item['option'])) {
                        $this->_errors[$item['item_key']][] = '値が存在しません。';
                        break;
                    }
                }
            }
            else if ($item['type'] == 'select') {
                if ($item['option_selected'] <= 0 || $item['option_selected'] > count($item['option'])) {
                    $this->_errors[$item['item_key']][] = '値が存在しません。';
                }
            }
        }

        // 電話番号の3項目チェック
        if (!$this->validate->isValidPersonTel(count($personTelItems))) {
            $this->_errors['person_tel'][] = $this->validate->errorMsgPersonTel();
            $params['person_tel'] = '';
        }

        $result = $this->apiContactValidate( $params ) ;
        if ( is_null( $result ) || $result->success == false ) {
            header( "location:/{$this->_base['filename']}/error/" ) ;
            exit ;
        }

        $this->_errors = array_merge($this->_errors, (array)$result->data->errors);
        if (!empty($this->_errors)) {
            return false;
        }
        return true;
    }

    private function errorMsgSelect($msg) {

        return "必須項目の $msg を入力してください。";
    }

    private function errorMsgInput($msg) {

        return "必須項目の $msg を入力してください。";
    }

    /**
     *
     */
    private function apiContactValidate($data) {

        $api = $this->_base['api_url'].'/validate';

        $params['company_id']       = $this->_auth['company_id'];
        $params['api_key']          = $this->_auth['api_key'];
        $params['data']             = $data;
        $options['http']['method']  = 'POST';
        $options['http']['header']  = implode( "\r\n", [ "Host: {$_SERVER['SERVER_NAME']}", 'Content-Type: application/x-www-form-urlencoded' ] ) ;
        $options['http']['content'] = http_build_query($params);

        $contents = json_decode(file_get_contents($api, false, stream_context_create($options)));
        return $contents;
    }

    private function createContactApiData($contactItems) {

        $contactData = [];
        foreach ($contactItems as $key => $val) {

            if ($val['item_key'] == 'connection') {
                foreach ($val['items'] as $connectioValue) {
                    $item             = [];
                    $item['item_key'] = $connectioValue['item_key'];
                    $item['label']    = $connectioValue['label'];
                    $item['value']    = $connectioValue['item_value'];
                    $contactData[]    = $item;
                }
                continue;
            }

            $item             = [];
            $item['item_key'] = $val['item_key'];
            $item['label']    = $val['label'];
            $item['value']    = '';

            // 面積 
            if ($val['item_key'] == 'property_exclusive_area' || $val['item_key'] == 'property_building_area' || $val['item_key'] == 'property_land_area') {
                $subOption = $val['sub_option'][$val['sub_option_checked'][0] - 1];
                if ($subOption == 'm<sup>2</sup>') {
                    $subOption = "平米";
                }
                $item['value'] = $val['item_value'].$subOption;
            }
            // 築年数
            else if ($val['item_key'] == 'property_age') {
                $item['value'] = $val['sub_option'][$val['sub_option_checked'][0] - 1].$val['item_value'];
            }
            // 間取り
            else if ($item['item_key'] == 'property_layout') {
                if ($val['option_selected'] >= 2 && $val['sub_option_selected'] >= 2) {
                    $item['value'] = $val['option'][$val['option_selected'] - 1].$val['sub_option'][$val['sub_option_selected'] - 1];
                }
            }
            // 要件
            else if ($val['item_key'] == 'subject') {
                $item['value']                   = $this->getOptionValue($val['option'], $val['option_checked']);
                $item['subject_more_item_key']   = $val['more_item_key'];
                $item['subject_more_item_value'] = $val['subject_more_item_value'];
            }
            //物件リクエスト
            else if ($val['item_key'] == 'request') {
                if ($val['type'] == 'select') {
                    if ($val['option_selected'] >= 2) {
                        $item['value'] = $this->getOptionValue($val['option'], $val['option_selected'] - 1);
                    }
                }
                // ラジオとチェックボックス
                else if ($val['type'] == 'radio' || $val['type'] == 'checkbox') {
                    $item['value'] = $this->getOptionValue($val['option'], $val['option_checked']);
                }
                else if ($val['type'] == 'text' || $val['type'] == 'textarea') {
                    $item['value'] = $val['item_value'];
                }
                $item['request_more_item_key']   = $val['more_item_key'];
                $item['request_more_item_value'] = $val['request_more_item_value'];
            }
            // テキスト
            else if ($val['type'] == 'text' || $val['type'] == 'textarea') {
                $item['value'] = $val['item_value'];
            }
            // セレクト
            else if ($val['type'] == 'select') {
                if ($val['option_selected'] >= 2) {
                    $item['value'] = $this->getOptionValue($val['option'], $val['option_selected'] - 1);
                }
            }
            // ラジオとチェックボックス
            else if ($val['type'] == 'radio' || $val['type'] == 'checkbox') {
                $item['value'] = $this->getOptionValue($val['option'], $val['option_checked']);
            }
            $contactData[] = $item;
        }
        return $contactData;
    }

    private function getOptionValue($option, $optionId) {

        if (!is_array($optionId)) {
            $value = $option[$optionId];
        }
        else {
            $value = [];
            foreach ($optionId as $key => $val) {
                $value[] = $option[$val - 1];
            }
        }

        return $value;
    }

    private function apiSend($contactItems, $clickDate) {

        $data['page_type_code'] = $this->_base['page_type_code'];
        $data['contact_mail']   = $this->_inquiry_mail;
        $data['contactItems']   = $this->createContactApiData($contactItems);
        $data['reply_mail']     = $this->_reply_mail;
        $data['site_type']      = $this->_context->siteType;
        $data['device']         = $this->ua->device();
        $data['useragent']      = $this->ua->useragent();
        $data['user_ip']        = $_SERVER["REMOTE_ADDR"];

        $data['fontend_send_date'] = $clickDate;

        $utime = sprintf('%.4f', microtime(TRUE));
        $raw_time = DateTime::createFromFormat('U.u', $utime);
        $raw_time->setTimezone(new DateTimeZone('Asia/Tokyo'));
        $gmoDate = $raw_time->format('Y-m-d H:i:s.u');
        $data['gmo_send_date'] = $gmoDate;

        $isDemoSite	= file_exists( APPLICATION_PATH . '/../../../isDemoSite' )	;
        if ( $isDemoSite == false ) {
        	$result = $this->apiContactSendInquiry($data);
        	if (is_null($result) || $result->success == false) {
        		header('location:'.'/'.$this->_base['filename'].'/error/');
        		exit;
        	}
        	if (!empty($result->data->errors)) {
        		return false;
        	}
        }
        return true;
    }

    private function apiContactSendInquiry($data) {

        $url                        = $this->_base['api_url'].'/send-inquiry';
        $params['company_id']       = $this->_auth['company_id'];
        $params['api_key']          = $this->_auth['api_key'];
        $params['data']             = $data;
        $options['http']['method']  = 'POST';
        $options['http']['header']  = implode("\r\n", ['Content-Type: application/x-www-form-urlencoded']);
        $options['http']['content'] = http_build_query($params);
        $contents                   = json_decode(file_get_contents($url, false, stream_context_create($options)));

        return $contents;
    }
}