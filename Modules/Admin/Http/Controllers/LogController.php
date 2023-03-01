<?php
namespace Modules\Admin\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Modules\Admin\Http\Form\LogSearch;
use App\Repositories\LogEdit\LogEditRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\PublishProgress\PublishProgressRepositoryInterface;
use Library\Custom\Model\Estate\ClassList;

use Library\Custom\Model\Lists\LogCsvHeader;
use Library\Custom\Model\Lists\CompanyAgreementType;
use Library\Custom\Model\Lists\LogEditType;

class LogController extends Controller
{
	public function index(Request $request) 
    {
    	// topicPath
    	$this->view->topicPath('ログ管理');
		$this->view->form = $form = new LogSearch();
        
		// パラメータ取得
		$paramsAll = $request->all();
        
        $form->setData($paramsAll);
		if( !$request->has("submit") || $request->submit == "" || !$form->isValid($paramsAll)) {
		    return view('admin::log.index');
        }

        // パラメータ情報
        $params = $this->getInputParams($paramsAll);

		// ログ種別がなければ終了
        if( $params->logType == "" ){
            return redirect()->back();
        }

        switch($params->logType) {
            case config('constants.log_type.LOGIN'):
            case config('constants.log_type.CREATE'):
            case config('constants.log_type.COMPANY'):
                // ログデータを取得
                $rows_arr = $this->getLogData( $params );

                if ($rows_arr !== false) {
                    // ログを出力する
                    $this->outputCSV($params, $rows_arr);
                }
                break;
            case config('constants.log_type.PUBLISH'):    // 公開処理
                $rows_arr = $this->outputPublishLogData( $params );
                break;
            default:
                throw new \Exception("Unexpected logType");
                break;
        }
        return view('admin::log.index');
    }

    /**
     * ajax
     */
    public function enableOutput(Request $request)
    {
        $form = new LogSearch();
        // パラメータ取得
        $paramsAll = $request->all();
        $form->setData($paramsAll);
        if( !$request->has("submit") || $request->submit == "" || !$form->isValid($paramsAll)) {
            throw new \Exception("不正なアクセスです。");
        }
        // パラメータ情報
        $params = $this->getInputParams($paramsAll);
        // ログ種別がなければ終了
        if( $params->logType == "" ){
            throw new \Exception("No Log Type.");
        }
        if (!$request->isMethod('post')) {
            throw new \Exception("不正なアクセスです。");
        }
        header("Content-Type: application/json; charset=UTF-8");
        switch($params->logType) {
            case config('constants.log_type.LOGIN'):
            case config('constants.log_type.CREATE'):
            case config('constants.log_type.COMPANY'):
                $rows_arr = $this->getLogData( $params );
                echo json_encode($rows_arr);
                break;
            case config('constants.log_type.PUBLISH'):    // 公開処理
                $rows_arr = $this->outputPublishLogData( $params );
                echo json_encode($rows_arr);
                break;
            default:
                break;
        }
        exit;
    }


    /** 入力パラメータを取得する
     * @return object
     */
    private function getInputParams($request) {

        $params = [];
        $params['logType']       = $this->getParamVal("log_type", $request);
        $params['memberNo']      = $this->getParamVal("member_no", $request);
        $params['companyName']   = $this->getParamVal("company_name", $request);
        $params['athomeStaffId'] = $this->getParamVal("athome_staff_id", $request);
        $params['datetimeS']     = $this->getParamVal("datetime_s", $request);
        $params['datetimeE']     = $this->getParamVal("datetime_e", $request);
        return (object) $params;
    }


    /** パラメータ値を取得する
     * @param $name
     * @return mixed|string|bool
     */
    private function getParamVal($name,$request) {
        $val = "";
        if( isset($request[$name]) && $request[$name] != "") {
            $val = $request[$name];
        }
        return $val;
    }


    /** ログデータを取得する
     * @param $params
     * @return array ログデータ
     */ 
    private function getLogData($params) {
 
        // CMS操作ログ情報を取得する
        $logObj =  App::make(LogEditRepositoryInterface::class);
        $select = $logObj->getDataLogForCompany($params);
        //検索条件
        if ( $params->logType == config('constants.log_type.CREATE') ) {
            $select->join("manager as m", "log_edit.athome_staff_id", "=" ,"m.id");
            $select->select("log_edit.*", "c.member_no", "c.member_name", "c.company_name", "c.contract_type", "m.name");
        }

        //担当者ID
        if($params->athomeStaffId != "") 
        {
            switch($params->logType) {
                //代行ログイン
                case config('constants.log_type.LOGIN') :
                    $select->where("log_edit.athome_staff_id", "like", '%'.$params->athomeStaffId. '%');
                    break;

                //代行作成
                case config('constants.log_type.CREATE') :
                    $select->where("m.name", "like", '%'.$params->athomeStaffId. '%');
                    break;
            }
        }
       
        //会員No
        if($params->memberNo != "") {
            $select->where("c.member_no", "like", '%'.$params->memberNo. '%');
        }
        
        //会社名
        if($params->companyName != "") {
            $select->where("c.company_name","like", '%'.$params->companyName. '%');
        }
        
        //操作日時
        if ($params->athomeStaffId != "" || $params->memberNo != "" || $params->companyName != "") {
         
            $isEnable = $this->setDatetimeParams($select, $params, true);
        } else {
            $isEnable = $this->setDatetimeParams($select, $params, false);
        }

        if ($isEnable === false) {
            return false;
        }

        $rows=$select->get();
        $rows_arr = $rows->toArray();
        return $rows_arr;

    }

    private function setDatetimeParams($select, $params, $isSpecified) {
        $dt = new \Datetime();
        $dt->setTimeZone(new \DatetimeZone('Asia/Tokyo'));
        $currentDate = $dt->format('Y-m-d');
        if ($isSpecified) {
            $maxDatetime = '180';
        } else {
            $maxDatetime = '31';
            if ($params->logType == config('constants.log_type.COMPANY')) {
                $maxDatetime = '14';
            }
        }
        // 操作日時（開始）・操作日時（終了）のいずれも指定されていない場合
        if ($params->datetimeS == '' && $params->datetimeE == '') {
            $sub = $dt->sub(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("log_edit.datetime", ">=", $sub->format('Y-m-d') . " 00:00:00");
            $select->where("log_edit.datetime" ,"<=", $currentDate . " 23:59:59");
            return $select;
        }
        // 操作日時（開始）のみが指定されている場合
        if ($params->datetimeS != "" && $params->datetimeE == "") {
            $dt = new \Datetime($params->datetimeS);
            $add = $dt->add(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("log_edit.datetime", "!=", '0000-00-00 00:00:00');
            $select->where("log_edit.datetime", ">=", $params->datetimeS .":00");
            $select->where("log_edit.datetime", "<=", $add->format('Y-m-d') . " 23:59:59");
            return $select;
        }
        // 操作日時（終了）のみが指定されている場合
        if ($params->datetimeS == "" && $params->datetimeE != "") {
            $dt = new \Datetime($params->datetimeE);
            $sub = $dt->sub(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("log_edit.datetime >= ?", $sub->format('Y-m-d') . " 00:00:00");
            $select->where("log_edit.datetime", "!=", '0000-00-00 00:00:00');
            $select->where("log_edit.datetime", "<=", $params->datetimeE .":59");
            return $select;
        }
        // 操作日時（開始）・操作日時（終了）のいずれも指定されている場合
        if ($params->datetimeS != "" && $params->datetimeE != "") {
            $start = new \Datetime($params->datetimeS);
            $end = new \Datetime($params->datetimeE);
            $diff = $end->diff($start);
            if ($diff->format('%a') > $maxDatetime) {
                return false;
            }
            $select->where("log_edit.datetime" ,">=", $params->datetimeS .":00");
            $select->where("log_edit.datetime" ,"<=", $params->datetimeE .":59");
            return $select;
        }
    }

    private function outputCSV($params, $rows_arr)
    {
        //CSV対象カラム名
        $csv_header = LogCsvHeader::getCsvHeader();
        
        //CSV表示カラム名
        $csv_header_name = LogCsvHeader::getCsvHeaderName();

        // 出力
        $fileName = "log_" . date("YmdHis") . ".csv";
        header("Pragma: public");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);

        $stream = fopen('php://output', 'w');
        $csv_row_name = array();
        foreach($csv_header_name as $name) {

            if ($name == "担当者ＣＤ"){
                if($params->logType == config('constants.log_type.CREATE') ) {
                    $name = "担当者名";
                }
            }

            mb_convert_variables('SJIS-win', 'UTF-8', $name);
            $csv_row_name[] = $name;
        }

        fputcsv($stream, $csv_row_name);

        $csvs = array();

        foreach($rows_arr as $key => $row) {

            //代行作成ログの場合は、管理者の名前を使用する
            if($params->logType == config('constants.log_type.CREATE')) {
                $row['athome_staff_id'] = $row['name'];
            }

            //操作内容
            $row['edit_type_code'] = $this->makeEditString($params,$row);

            //物件種別
            $row['contract_type'] = CompanyAgreementType::getInstance()->get( $row['contract_type'] );

            //
            $csv_row = array();
            foreach($csv_header as $name) {

                if($name == "page_id") {
                    continue;
                }

                mb_convert_variables('SJIS-win', 'UTF-8', $row[$name]);
                $csv_row[] = '="'.(string)$row[$name]. '"';
            }
            fputcsv($stream, $csv_row);
        }
        fclose($stream);
        exit;

    }

    private function makeEditString($params,$val){

        //操作ログ系の内容
        $log_edit_type_list = LogEditType::getInstance()->getAll();

        $hpPageObj = App::make(HpPageRepositoryInterface::class);

        // ATHOME_HP_DEV-5329 強制:delete_flg=0を無効化
        // $hpPageObj->setAutoLogicalDelete(false);

        $editStr = "";
        $editTypeCode =  $val['edit_type_code'];

        if(!isset($log_edit_type_list[$editTypeCode])) {
            return $editStr;
        }

        //ページ系
        if (LogEditType::isPageLog($editTypeCode)) {
            $page_name = "";
            $where = array(array("id", $val['page_id']));
            $row = $hpPageObj->fetchRow($where);
            if($row != null) {
                // ATHOME_HP_DEV-5329 hp_page.id をログに記載する
                $page_name = $row->getTypeNameJp() . "(" . $val['page_id'] . ")" . " / ";
            }
            $editStr = $page_name . $log_edit_type_list[$editTypeCode];

        //物件設定系
        } else if (LogEditType::isEstateLog($editTypeCode)) {
            $attr = [];
            if ($val['attr1'] != ""){
                $attr = (object)json_decode($val['attr1']);
            }

            $attrStr = "";
            if(isset($attr->class)){
                $attrStr = ClassList::getInstance()->get($attr->class). " / ";
            }
            $editStr =  $attrStr . $log_edit_type_list[$editTypeCode];

        //特集設定系
        } else if (LogEditType::isSpecialLog($editTypeCode)) {
            if ($val['attr1'] != ""){
                $attr = (object)json_decode($val['attr1']);
            }

            $attrStr = "";
            if(isset($attr->filename) && isset($attr->title) ){
                $attrStr = $attr->title."(".$attr->filename.")". " / ";
            }
            $editStr =  $attrStr.$log_edit_type_list[$editTypeCode];

        //２次広告自動公開設定系
        } else if (LogEditType::isSecondEstateLog($editTypeCode)) {
            if ($val['attr1'] != ""){
                $attr = (object)json_decode($val['attr1']);
            }

            $attrStr = "";
            if(isset($attr->class)){
                $attrStr = ClassList::getInstance()->get($attr->class). " / ";
            }
            $editStr =  $attrStr . $log_edit_type_list[$editTypeCode];

        }
        //その他
        else
        {
            $editStr =  $log_edit_type_list[$editTypeCode];
        }

        return $editStr;
    }

    /** 公開ログデータをCSV出力する
     * @param $params
     */
    private function outputPublishLogData($params) {
        // 公開処理情報を取得するためテーブル:companyと結合
        $logObj = App::make(PublishProgressRepositoryInterface::class);
        $select = $logObj->getDataPublishForCompany();
        // $select->limit(1000);   // 直近1000件まで

        // 会員No(c.member_no)
        if($params->memberNo != "") {
            $select->where("c.member_no", "like", '%'.$params->memberNo. '%');
        }
        // 会社名(c.company_name)
        if($params->companyName != "") {
            $select->where("c.company_name", "like", '%'.$params->companyName. '%');
        }
        // 操作日時(publish_progress.start_time)
        if ($params->memberNo != "" || $params->companyName != "") {
            $isEnable = $this->setDatetimeParamsPublish($select, $params, true);
        } else {
            $isEnable = $this->setDatetimeParamsPublish($select, $params, false);
        }

        if ($isEnable === false) {
            return false;
        }

        $rows = $select->get();
        $rows_arr = $rows->toArray();

        // CSV出力
        $fileName = "log_" . date("YmdHis") . ".csv";

        $header = [ '会員ＮＯ', '会員名', '会社名', '契約種別', '代行ログイン担当者ＣＤ',
                    '全公開/差分公開', '公開先', '操作日時', '完了日時', '正常/異常',
                    'エラー内容', '進捗履歴' ];

        header("Pragma: public");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $fileName);
        $stream = fopen('php://output', 'w');

        fwrite($stream, pack('C*',0xEF,0xBB,0xBF)); // BOM出力

        fputcsv($stream, $header);

        foreach ($rows_arr as $rowHash) {
            $row = [];

            // - 会員ＮＯ -
            $row[] = $rowHash['member_no'];
            // - 会員名 -
            $row[] = $rowHash['member_name'];
            // - 会社名 -
            $row[] = $rowHash['company_name'];
            // - 契約種別 -
            $row[] = CompanyAgreementType::getInstance()->get( $rowHash['contract_type'] );
            // - 操作アカウント or バッチ -
            $row[] = $rowHash['login_id'];
            // - 全公開 or 差分公開
            $row[] = ($rowHash['all_upload_flg']) ? '全公開' : '差分公開';
            // - 公開先 -
            switch($rowHash['publish_type']) {
                case "1":
                    $row[] = '本番';
                    break;
                case "2":
                    $row[] = 'テストサイト';
                    break;
                case "3":
                    $row[] = '代行サイト';
                    break;
            }
            // - 操作日時 -
            $row[] = $rowHash['start_time'];
            // - 完了日時 -
            $row[] = $rowHash['finish_time'];
            // - 結果 -
            if(is_null($rowHash['status'])) {
                $row[] = '(処理中)';
            } else {
                $row[] = ($rowHash['status']) ? '正常終了' : '異常終了';
            }
            // - エラー内容 -
            $row[] = $rowHash['exception_msg'];
            // - 進捗履歴 - : 改行を外す
            $row[] = preg_replace("/\r\n|\r|\n/", "->", rtrim($rowHash['progress']));

            fputcsv($stream, $row);
        }
        fclose($stream);
        exit;

    }

    private function setDatetimeParamsPublish($select, $params, $isSpecified) {
        $dt = new \Datetime();
        $dt->setTimeZone(new \DatetimeZone('Asia/Tokyo'));
        $currentDate = $dt->format('Y-m-d');
        if ($isSpecified) {
            $maxDatetime = '180';
        } else {
            $maxDatetime = '31';
        }
        // 操作日時（開始）・操作日時（終了）のいずれも指定されていない場合
        if ($params->datetimeS == '' && $params->datetimeE == '') {
            $sub = $dt->sub(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("publish_progress.start_time", ">=", $sub->format('Y-m-d') . " 00:00:00");
            $select->where("publish_progress.start_time", "<=", $currentDate . " 23:59:59");
            return $select;
        }
        // 操作日時（開始）のみが指定されている場合
        if ($params->datetimeS != "" && $params->datetimeE == "") {
            $dt = new \Datetime($params->datetimeS);
            $add = $dt->add(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("publish_progress.start_time", "!=", '0000-00-00 00:00:00' );
            $select->where("publish_progress.start_time", ">=", $params->datetimeS .":00");
            $select->where("publish_progress.start_time", "<=", $add->format('Y-m-d') . " 23:59:59");
            return $select;
        }
        // 操作日時（終了）のみが指定されている場合
        if ($params->datetimeS == "" && $params->datetimeE != "") {
            $dt = new \Datetime($params->datetimeE);
            $sub = $dt->sub(new \DateInterval('P' . $maxDatetime . 'D'));
            $select->where("publish_progress.start_time", ">=", $sub->format('Y-m-d') . " 00:00:00");
            $select->where("publish_progress.start_time", "!=", '0000-00-00 00:00:00');
            $select->where("publish_progress.start_time", "<=", $params->datetimeE .":59");
            return $select;
        }
        // 操作日時（開始）・操作日時（終了）のいずれも指定されている場合
        if ($params->datetimeS != "" && $params->datetimeE != "") {
            $start = new \Datetime($params->datetimeS);
            $end = new \Datetime($params->datetimeE);
            $diff = $end->diff($start);
            if ($diff->format('%a') > $maxDatetime) {
                return false;
            }
            $select->where("publish_progress.start_time" ,">=", $params->datetimeS .":00");
            $select->where("publish_progress.start_time" ,"<=", $params->datetimeE .":59");
            return $select;
        }
    }
}

