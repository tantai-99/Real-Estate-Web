<?php

require_once(APPLICATION_PATH.'/../script/_AbstractGateway.php');
require_once(APPLICATION_PATH.'/../script/Contact.php');
require_once(APPLICATION_PATH.'/../script/Validate.php');
require_once(APPLICATION_PATH.'/../script/Maxlength.php');

/*
 * 物件お問い合わせフォーム
 *
 */
define('APOSTROPHE_MARKS', "'");
define('QUOTATION_MARKS', '"');
abstract class _AbstractEstateContact extends _AbstractGateway {
    
    public function __construct($config) {

        parent::__construct($config);

        $this->_token     = md5(uniqid(rand(),1)); 
        $this->_window    = $this->request->getPost('target'); 

        if (is_null($this->_window)){
            $this->_window = $_GET['window']; 
        }

        $settingPath = APPLICATION_PATH.'/../setting';
        if (!$this->_contactName) {
            $this->_contactName = $this->request->parse['path_array'][1];
        }
        $iniFile = glob($settingPath."/contact_".$this->_contactName."_*.ini")[0];

        // Blowfish-Salt生成
        $this->_iniSalt = '$2a$07$' . substr(md5_file($iniFile), 0, 21) . 'u';

        $contactIni          = parse_ini_file($iniFile, true);
        $this->_auth         = $contactIni['auth'];
        $this->_base         = $contactIni['base'];
        $this->_inquiry_mail = $contactIni['inquiry_mail'];
        $this->_reply_mail   = $contactIni['reply_mail'];
        $this->_contactIni   = $contactIni;
        $this->_sitetype     = $this->apiGateway->getSitetype();
        $this->_errors       = [];
        $this->_contactItems = $this->getContactItem();
        $this->validate      = new Validate();
        $this->view->contact = new Contact(new stdClass());


    }

    protected function init() {
    }

    protected function runEdit() {

        //確認画面からの戻り
        if (isset($_POST['back'])) {
            $params = $this->request->getPostAll();
            $this->_contactItems = $this->populate($params);
            $this->_token = $this->request->getPost('token');
            $bukken_id_csv = $this->request->getPost('bukken_id_csv');
            $bukken_type      = $this->request->getPost('bukken_type');
            $contact_type = $this->request->getPost('contact_type');
            $selectedEstate = $this->request->getPost('selectedEstate');
            $special_id = $this->request->getPost('special_id');
            $recommend_flg = $this->request->getPost('recommend_flg');
            $from_searchmap = $this->request->getPost('from_searchmap');
            // お問い合わせ対象の物件ID
            $this->view->page = 'edit';
            $this->view->token = $this->_token;
            $this->view->bukken_id_csv = $bukken_id_csv;
            $this->view->bukken_type = $bukken_type;
            $this->view->contact_type = $contact_type;
            $this->view->special_id = $special_id;
            $this->view->recommend_flg = $recommend_flg;
            $this->view->from_searchmap = $from_searchmap;
            //初回
        }else{
            // 物件IDがpostされていなければエラーにする
            if ( is_null($this->request->getPost('token')) && is_null($this->request->getPost('id'))) {
                header('location:'.'/inquiry/bukken/error/');
                exit;
            }

            $bukken_id_csv = $this->request->getPost('id');
            $bukken_type      = $this->request->getPost('type');
            $contact_type     = $this->request->parse['path_array'][1];
            $special_id       = is_null($this->request->getPost('special-path')) ? "" : $this->request->getPost('special-path');
            $recommend_flg    = 0;
            if (!is_null($this->request->getPost('from-recommend')) && $this->request->getPost('from-recommend') == 'true') {
                $recommend_flg = 1;
            }
            $from_searchmap    = 0;
            if (!is_null($this->request->getPost('from_searchmap')) && $this->request->getPost('from_searchmap') == 'true') {
                $from_searchmap = 1;
            }

            // お問い合わせ対象の物件ID
            $this->view->page = 'edit';
            $this->view->token = $this->_token;
            $this->view->bukken_id_csv = $bukken_id_csv;
            $this->view->bukken_type = $bukken_type;
            $this->view->contact_type = $contact_type;
            $this->view->special_id = $special_id;
            $this->view->recommend_flg = $recommend_flg;
            $this->view->from_searchmap = $from_searchmap;

            $selectedEstate = explode(',', $bukken_id_csv);
        }

        // 連携サーバーのレスポンス
        $res = $this->fetchContact($bukken_id_csv, $bukken_type, $contact_type);

        $this->view->estateListElement = $res->api->content;

        $this->view->base            = $this->_base;
        $this->view->estatelist      = $this->populateBukkenList($res->api->content, (array)$selectedEstate);
        $this->view->api             = $res->api; // head
        $this->view->contactItems    = $this->_contactItems;
        $this->view->urlName         = $this->_urlName;
        $this->view->token           = $this->_token;
        $this->view->window          = $this->_window;
        $this->view->errors          = $this->_errors;
        $this->view->policy          = $this->getPrivacyHtml();
        $this->view->policyFilename  = $this->getPrivacyFilename();
        $this->view->elementTemplate = str_replace('-', '_', $this->_urlName).'.php';

        return $this->view;
    }

    protected function validateByAjax() {
        $params = $this->request->getPostAll();
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

    protected function runConfirm() {
        //$_Postから値を取り出して整形する処理
        //次へアクション
        if (!is_null($this->request->getPost('next'))) {

            if (!$this->request->getPost('token')){
                header('location:'.'/inquiry/bukken/error/');
                exit;
            }
            $params = $this->request->getPostAll();
            $this->_contactItems = $this->populate($params);

            $bukken_id_csv = $this->request->getPost('bukken_id_csv');
            $bukken_type      = $this->request->getPost('bukken_type');
            $contact_type = $this->request->getPost('contact_type');
            $this->_token = $this->request->getPost('token');
            $selectedEstate = $this->request->getPost('bukken_id');
            $special_id = $this->request->getPost('special_id');
            $recommend_flg = $this->request->getPost('recommend_flg');
            $from_searchmap = $this->request->getPost('from_searchmap');
            // お問い合わせ対象の物件ID
            $this->view->token = $this->_token;
            $this->view->bukken_id_csv = $bukken_id_csv;
            $this->view->bukken_type = $bukken_type;
            $this->view->contact_type = $contact_type;
            $this->view->special_id = $special_id;
            $this->view->recommend_flg = $recommend_flg;
            $this->view->from_searchmap = $from_searchmap;
            //EstateIdsselectedのこと
            $this->view->selectedEstate = $selectedEstate;
            $this->view->estateListElement = $this->request->getPost('estateListElement');
            $this->_estateInfo = array('estateIds'=>$selectedEstate);
            $this->view->estateInfo         = $this->_estateInfo;
            // 連携サーバーのレスポンス
            $res = $this->fetchContact(implode(',', $selectedEstate), $bukken_type, $contact_type);
            $notSelectedBukkenIdArray = array_diff((array)explode(',', $bukken_id_csv), $selectedEstate);

            $this->view->base         = $this->_base;
            $this->view->contactItems = $this->_contactItems;
            $this->view->urlName      = $this->_urlName;
            $this->view->estatelist   = $this->removeElement($this->request->getPost('estateListElement'), $notSelectedBukkenIdArray);
            $this->view->api          = $res->api; // head
            $this->view->token        = $this->_token ;
            $this->view->window       = $this->_window ;

            // CSRF代わり設定
            setcookie('enctoken', substr(crypt($this->_token, $this->_iniSalt), strlen($this->_iniSalt)), time() + 60 * 10, "/", null, true, true);

            return $this->view;

        }
        header('location:'.'/inquiry/bukken/error/');
        exit;
    }

    protected function runComplete() {
        if (!is_null($this->request->getPost('send'))) {
            $params = $this->request->getPostAll();
            $this->_token = $this->request->getPost('token');

            if ($this->_token == null){
                header('location:'.'/inquiry/bukken/error/');
                exit;
            }
            if(substr(crypt($this->_token, $this->_iniSalt), strlen($this->_iniSalt))  != $_COOKIE['enctoken']) {
                header('location:'.'/inquiry/bukken/error/');
                exit;
            }

            $this->_contactItems = $this->populate($params);
            $selectedBukkenIdArray = $this->request->getPost('selectedEstate');;
            $this->_estateInfo = array('estateIds'=>$selectedBukkenIdArray);
            // メールを送信する
            $result = $this->apiSend($this->_estateInfo, $this->_contactItems);
            if ($result == true) {
                // 有効期限を過去にしてenctokenのcookieを削除
                setcookie('enctoken', '', time() - 3600, '/');

                $bukken_id_csv = $this->request->getPost('bukken_id_csv');
                $bukken_type      = $this->request->getPost('bukken_type');
                $contact_type = $this->request->getPost('contact_type');
                // 連携サーバーのレスポンス
                $res = $this->fetchContact($bukken_id_csv, $bukken_type, $contact_type);

                $this->view->base         = $this->_base;
                $this->view->urlName      = $this->_urlName;
                $this->view->api          = $res->api; // head
                return $this->view;
            }
        }
        header('location:'.'/inquiry/bukken/error/');
    }

    protected function fetchContact($estateIdsString, $type_ct, $contact_type) {
        // base url
        $url = $this->apiUrl();
        $url .= $this->addParam(ApiGateway::KEY_BUKKEN_ID, $estateIdsString);
        $url .= $this->addParam('type_ct', $type_ct);
        $url .= $this->addParam('contact_type', $contact_type);

        // fetch
        return $this->fetch($url);
    }

    protected function getContactItem() {


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
                $val['option_checked'] = ($val['item_key'] == 'hankyo_plus' ? 1 : []);
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


    protected function populate($params) {

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
                else {
                    $val['option_checked'] = [];
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
            $contactItems[] = $val;
        }

        return $contactItems;
    }

    /* 
     * 
     */
    protected function validate($contactItems, $params) {
        
        $this->_errors = [];

        if (is_null($params['bukken_id'])) {
            $this->_errors[] = "物件が選択されていません。";
        }
        
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
                    if ($item['option_selected'] == '1') {
                        $this->_errors[$item['item_key']][] = $this->errorMsgSelect($item['label']);
                    }
                }
                else if ($item['type'] == 'text' || $item['type'] == 'textarea') {
                    if (empty($item['item_value'])) {
                        $this->_errors[$item['item_key']][] = $this->errorMsgInput($item['label']);
                    }
                }
            }

            if ($item['type'] == 'checkbox' || $item['type'] == 'radio') {
                $maxNum = ($item['item_key'] == 'peripheral' || $item['item_key'] == 'hankyo_plus') ? 1 : count($item['option']);
                foreach ($item['option_checked'] as $checked) {
                    if ($checked <= 0 || $checked > $maxNum) {
                        $this->_errors[$item['item_key']][] = '値が存在しません。';
                    }
                }
            }
            else if ($item['type'] == 'select') {
                if ($item['option_selected'] > count($item['option'])) {
                    $this->_errors[$item['item_key']][] = '値が存在しません。';
                }
            }
        }

        // 電話番号の3項目チェック
        if (!$this->validate->isValidPersonTel(count($personTelItems))) {
            $this->_errors['person_tel'][] = $this->validate->errorMsgPersonTel();
            $params['person_tel'] = '';
        }

        $result = $this->apiContactValidate($params);
        if (is_null($result) || $result->success == false) {
            header('location:'.'/'.$this->_base['filename'].'/error/');
            exit;
        }

        $this->_errors = array_merge($this->_errors, (array)$result->data->errors);
        if (!empty($this->_errors)) {
            return false;
        }
        return true;
    }

    protected function errorMsgSelect($msg) {

        return "必須項目の $msg を入力してください。";
    }

    protected function errorMsgInput($msg) {

        return "必須項目の $msg を入力してください。";
    }

    /**
     *
     */
    protected function apiContactValidate($data) {

        $api = $this->_base['api_url'].'/validate';

        $params['company_id']       = $this->_auth['company_id'];
        $params['api_key']          = $this->_auth['api_key'];
        $params['data']             = $data;
        $options['http']['method']  = 'POST';
        $options['http']['header']  = implode("\r\n", ['Content-Type: application/x-www-form-urlencoded']);
        $options['http']['content'] = http_build_query($params);

        $contents = json_decode(file_get_contents($api, false, stream_context_create($options)));
        return $contents;
    }


    protected function apiSend($estateInfo, $contactItems) {
        $data['page_type_code'] = $this->_base['page_type_code'];
        $data['contact_mail']   = $this->_inquiry_mail;
        $data['estateInfo']     = $estateInfo;
        $data['contactItems']   = $this->createContactApiData($contactItems);
        $data['reply_mail']     = $this->_reply_mail;
        $data['site_type']      = $this->_sitetype;
        $data['special_id']     = $this->request->getPost('special_id');
        $data['recommend_flg']  = $this->request->getPost('recommend_flg');
        $data['from_searchmap'] = (!$this->request->getPost('from_searchmap')) ? 0 : 1;
        $data['bukken_type']    = $this->request->getPost('bukken_type');
        $data['device']         = $this->ua->device();
        $data['useragent']      = $this->ua->useragent();
        $data['user_ip']        = $_SERVER["REMOTE_ADDR"];
        // 4293 Add contact log FDP
        $data['peripheral_flg']  = empty($this->request->getPost('peripheral')) ? 0 : 1;
        $data['user_id']        = $this->request->getCookie('user_id');
        $data['hankyo_plus_use_flg']  = empty($this->request->getPost('hankyo_plus')) ? 0 : 1;
        
        $result = $this->apiContactSendInquiry($data);
        if (is_null($result) || $result->success == false) {
            header('location:'.'/inquiry/bukken/error/');
            exit;
        }
        if (!empty($result->data->errors)) {
            return false;
        }
        return true;
    }

    protected function apiContactSendInquiry($data) {
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


    protected function createContactApiData($contactItems) {

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

            // 要件
            if ($val['item_key'] == 'subject') {
                $item['value']                   = $this->getOptionValue($val['option'], $val['option_checked']);
                $item['subject_more_item_key']   = $val['more_item_key'];
                $item['subject_more_item_value'] = $val['subject_more_item_value'];
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

    protected function getOptionValue($option, $optionId) {

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

    /**
     * プライバシーポリシーのHTMLを取得
     *
     * @return string
     */
    private function getPrivacyHtml(){
        $privacyFilename = $this->getPrivacyFilename();
        if( !$privacyFilename ){
            return "";
        }
        $contents = file_get_contents(APPLICATION_PATH.'/pc/'.$privacyFilename.'/index.html');
        $contents = mb_convert_encoding($contents, 'HTML-ENTITIES', 'auto');
        $doc = phpQuery::newDocument($contents);
        return $doc['.policy']->html();
    }

    /**
     * 物件リストのチェック
     *
     * @param       $contents
     * @param array $bukkenIdList
     * @return String
     */
    private function populateBukkenList($contents, array $bukkenIdList) {

        if (count($bukkenIdList) < 1) {

            return $contents;
        }

        $doc = phpQuery::newDocument($contents);

        foreach ($bukkenIdList as $bukkenId) {
            $selector = 'input[value="'.$bukkenId.'"]';
            $doc[$selector]->attr('checked', 'checked');
        }

        return $doc->htmlOuter();
    }

    /**
     * 物件一覧の要素を削除
     * - 確認画面用
     * -- チェックボックス
     * -- 選択されていない物件
     *
     * @param $contents
     * @return string
     */
    private function removeElement($contents, array $notSelectedBukkenIdArray) {

        $doc = phpQuery::newDocument($contents);

        switch ($this->ua->requestDevice()) {
            case 'pc':
                // remove not selected estate
                foreach ($notSelectedBukkenIdArray as $id) {
                    pq($doc["[value={$id}]"])->parents('tr')->remove();
                }
                // remove checkbox
                $doc['th:first']->remove();
                $doc['td:has(:checkbox)']->remove();
                break;
            case  'sp':
                // remove not selected estate
                foreach ($notSelectedBukkenIdArray as $id) {
                    pq($doc["[data-bukken-no={$id}]"])->remove();
                }
                // remove checkbox
                $doc['label:has(:checkbox)']->remove();
                break;
        }

        return $doc->htmlOuter();
    }

    /**
     * viewにパラメータをセット
     *
     * @param array $params
     */
    private function setView(array $params) {

        foreach ($params as $i => $v) {
            $this->view->{$i} = $v;
        }
    }

    /**
     * リダイレクト
     *
     * @param $url
     */
    private function redirectTo($url) {

        header("location:{$url}");
        exit;
    }

    /**
     * エラー画面へリダイレクト
     *
     */
    private function redirectToError() {

        $this->redirectTo('/inquiry/bukken/error/');
    }

}