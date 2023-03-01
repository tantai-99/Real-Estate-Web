<?php

namespace Modules\V1api\Models\Logger;

use Library\Custom\Registry;
use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;
use App\Repositories\Company\CompanyRepositoryInterface;
use Library\Custom\Mail;

class CLogger
{
    // CLOGファイルの桁数
    const DATE_DIGIT = 8;
    const TIME_DIGIT = 6;
    const COMPANY_ID = 10;
    const BUKKEN_TYPE = 10;
    const SPECIAL_ID = 10;
    const MOBILE = 2;
    const BUKKEN_NUMBER = 10;
    const NIJI_KOKOKU_JIDO_KOKAI = 1;
    const RECOMMEND_BUKKEN = 1;
    const MAP_SEARCH = 1;
    const KAIIN_NUMBER = 10;
    const PANORAMA_CONTENT = 10;

    private static $instance;
    private $dateObj;
    protected $logger;
    protected $_config;
    protected $clog_path;
    // ph3で新フォーマットにする日付
    private static $ph3_date = '2016/12/15 00:00:00';

    public function __construct()
    {
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');

        $this->clientS3 = \Aws\S3\S3Client::factory(array(
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest'
          ));
        $this->clientS3->registerStreamWrapper();

        $this->clog_path = 's3://'.env('AWS_BUCKET') . "/logs/estate_log/";

        $dt = new \DateTime(null, new \DateTimeZone('Asia/Tokyo'));
        $this->dateObj = new \stdClass();
        $this->dateObj->date = $dt->format('Ymd');
        $this->dateObj->time = $dt->format('His');
    }

    public static function logMap(
        Params $params,
        $class,
        $special_id
    ) {
        $comId = $params->getComId();
        $class = sprintf('%02d', $class);
        $special_id = $special_id;
        $agent = $params->isPcMedia() ? '00' : '02';
        $user_agent = static::getUserAgent();
        $user_ip = $params->getUserIp();
        $details = [
            $comId,
            '"' . $class . '"',
            $special_id,
            '"' . $agent . '"',
            '"' . $user_ip . '"',
            '"' . $user_agent . '"'
        ];
        $header_names = array(
            '"日付"',
            '"時刻"',
            '"加盟店ID"',
            '"物件種別"',
            '"アドバンス特集ID"',
            '"PC/モバイル区分"',
            '"ユーザIP"',
            '"ユーザーエージェント"'
        );
        $cLogger = static::getInstance();
        if ($cLogger->isWriteLog('agm', $details, $params)) {
            $cLogger->log("agm", $details, $header_names);
        } else {
            $companyObj = \App::make(CompanyRepositoryInterface::class)->fetchRow(array("id = ?" => $comId));
            $cLogger->sendMail($companyObj);
        }
    }

    public static function logResult(
        Params $params,
        $class,
        $special_id
    ) {
        $comId = $params->getComId();
        $class = sprintf('%02d', $class);
        $special_id = $special_id;
        $agent = $params->isPcMedia() ? '00' : '02';
        $user_ip = $user_ip = $params->getUserIp();
        $user_agent = static::getUserAgent();
        if (static::new_format()) {
            $details = [
                $comId,
                '"' . $class . '"',
                $special_id,
                '"' . $agent . '"',
                '"' . $user_ip . '"',
                '"' . $user_agent . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"アドバンス特集ID"',
                '"PC/モバイル区分"',
                '"ユーザIP"',
                '"ユーザーエージェント"'
            );
        } else {
            $details = [
                $comId,
                '"' . $class . '"',
                $special_id,
                '"' . $agent . '"',
                '"' . $user_agent . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"アドバンス特集ID"',
                '"PC/モバイル区分"',
                '"ユーザーエージェント"'
            );
        }
        $cLogger = static::getInstance();
        if ($cLogger->isWriteLog('agx', $details, $params)) {
            $cLogger->log("agx", $details, $header_names);
        } else {
            $companyObj = \App::make(CompanyRepositoryInterface::class)->fetchRow(array("id = ?" => $comId));
            $cLogger->sendMail($companyObj);
        }
    }

    public static function logDetail(
        Params $params,
        $class,
        $bukken_no,
        $isNijiKokokuJidou,
        $special_id,
        $isOsusume,
        $bukken_id,
        $version_no
    ) {
        $comId = $params->getComId();
        $class = sprintf('%02d', $class);
        $bukken_no = $bukken_no;
        $niji_kokoku_jido = $isNijiKokokuJidou ? '1' : '0';
        $special_id = $special_id;
        $osusume = $isOsusume ? '1' : '0';
        $agent = $params->isPcMedia() ? '00' : '02';
        $user_ip = $params->getUserIp();
        $from_searchmap = $params->getFromSearchmap();
        $user_agent = static::getUserAgent();

        if (static::new_format()) {
            $details = [
                $comId,
                '"' . $class . '"',
                '"' . $bukken_no . '"',
                '"' . $niji_kokoku_jido . '"',
                $special_id,
                '"' . $osusume . '"',
                '"' . $from_searchmap . '"',
                '"' . $agent . '"',
                '"' . $user_ip . '"',
                '"' . $user_agent . '"',
                '"' . $bukken_id . '"',
                '"' . $version_no . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"物件番号"',
                '"２次広告自動公開フラグ"',
                '"特集ID"',
                '"おすすめ物件フラグ"',
                '"地図検索遷移フラグ"',
                '"PC/モバイル区分"',
                '"ユーザIP"',
                '"ユーザーエージェント"',
                '"物件ID"',
                '"物件バージョン番号"',
            );
        } else {
            $details = [
                $comId,
                '"' . $class . '"',
                '"' . $bukken_no . '"',
                '"' . $niji_kokoku_jido . '"',
                $special_id,
                '"' . $osusume . '"',
                '"' . $agent . '"',
                '"' . $user_agent . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"物件番号"',
                '"２次広告自動公開フラグ"',
                '"特集ID"',
                '"おすすめ物件フラグ"',
                '"PC/モバイル区分"',
                '"ユーザーエージェント"',
            );
        }
        $cLogger = static::getInstance();
        if ($cLogger->isWriteLog('abk', $details, $params)) {
            $cLogger->log("abk", $details, $header_names);
        } else {
            $companyObj = \App::make(CompanyRepositoryInterface::class)->find($comId);
            $cLogger->sendMail($companyObj);
        }
    }

    public static function logPanorama(
        Params $params,
        $bukken_no,
        $member_no,
        $panorama_contents_id,
        $agent,
        $bukken_id,
        $version_no
    ) {
        $comId = $params->getComId();
        $user_ip = $params->getUserIp();
        $user_agent = static::getUserAgent();

        if (static::new_format()) {
            $panoramas = [
                $comId,
                '"' . $bukken_no . '"',
                '"' . $member_no . '"',
                '"' . $panorama_contents_id . '"',
                '"' . $agent . '"',
                '"' . $user_ip . '"',
                '"' . $user_agent . '"',
                '"' . $bukken_id . '"',
                '"' . $version_no . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件番号"',
                '"会員番号"',
                '"パノラマコンテンツID"',
                '"PC/モバイル区分"',
                '"ユーザIP"',
                '"ユーザーエージェント"',
                '"物件ID"',
                '"物件バージョン番号"',
            );
        } else {
            return;
        }
        $cLogger = static::getInstance();
        $clog_path = 's3://'.env('AWS_BUCKET') . "/logs/panorama_log/";
        if ($cLogger->isWriteLog('adp', $panoramas, $params)) {
            $cLogger->log("adp", $panoramas, $header_names, $clog_path);
        }
    }

    // 4293: Add log detail FDP
    public static function logDetailFDP(
        Params $params,
        $class,
        $bukken_no,
        $isNijiKokokuJidou,
        $special_id,
        $isOsusume,
        $tab
    ) {
        $comId = $params->getComId();
        $class = sprintf('%02d', $class);
        $bukken_no = $bukken_no;
        $niji_kokoku_jido = $isNijiKokokuJidou ? '1' : '0';
        $special_id = $special_id;
        $osusume = $isOsusume ? '1' : '0';
        $agent = $params->isPcMedia() ? '00' : '02';
        $user_ip = $params->getUserIp();
        $from_searchmap = $params->getFromSearchmap();
        $user_agent = static::getUserAgent();
        $refere = $params->getRefere() ? 0 : $tab;
        $tab = $tab - 1;
        if (static::new_format()) {
            $details = [
                $comId,
                '"' . $class . '"',
                '"' . $bukken_no . '"',
                '"' . $niji_kokoku_jido . '"',
                $special_id,
                '"' . $osusume . '"',
                '"' . $from_searchmap . '"',
                '"' . $agent . '"',
                '"' . $refere . '"',
                '"' . $tab . '"',
                '"' . $user_ip . '"',
                '"' . $user_agent . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"物件番号"',
                '"２次広告自動公開フラグ"',
                '"特集ID"',
                '"おすすめ物件フラグ"',
                '"地図検索遷移フラグ"',
                '"PC/モバイル区分"',
                '"リファラー"',
                '"表示ページ"',
                '"ユーザIP"',
                '"ユーザーエージェント"',
            );
        } else {
            $details = [
                $comId,
                '"' . $class . '"',
                '"' . $bukken_no . '"',
                '"' . $niji_kokoku_jido . '"',
                $special_id,
                '"' . $osusume . '"',
                '"' . $agent . '"',
                '"' . $user_agent . '"'
            ];
            $header_names = array(
                '"日付"',
                '"時刻"',
                '"加盟店ID"',
                '"物件種別"',
                '"物件番号"',
                '"２次広告自動公開フラグ"',
                '"特集ID"',
                '"おすすめ物件フラグ"',
                '"PC/モバイル区分"',
                '"ユーザーエージェント"',
            );
        }
        $cLogger = static::getInstance();
        if ($cLogger->isWriteLog('afd', $details, $params)) {
            $cLogger->log("afd", $details, $header_names);
        }
    }

    private static function getUserAgent()
    {
        $headers = getallheaders();
        $user_agent = isset($headers['HTTP_USER_AGENT']) ?
            $headers['HTTP_USER_AGENT'] : (isset($headers['User-Agent']) ? $headers['User-Agent'] : null);
        return $user_agent;
    }

    private function log($prefix, $details, $header_names, $clog_path = null)
    {
        $data = [
            '"' . $this->dateObj->date . '"',
            '"' . $this->dateObj->time . '"'
        ];
        foreach ($details as $one) {
            array_push($data, $one);
        }

        if (is_null($clog_path)) {
            $file_name = $this->clog_path . $prefix . $this->dateObj->date . '.txt';
        } else {
            $file_name = $clog_path . $prefix . $this->dateObj->date . '.txt';
        }
        // ヘッダカラム名は出力しない
        //         if(!file_exists($file_name)) {
        //             $this->fputcsv($file_name, $header_names);
        //         }
        $this->fputcsv($file_name, $data);
    }

    /**
     * ログに書き込みができるかどうか【桁数チェック】
     * @param string $prefix ログファイルのprefix
     * @param array $data ログ出力されるデータ
     * @param object $params リクエストのデータ
     */
    private function isWriteLog($prefix, $data, $params)
    {
        // 加盟店IDは共通で確認をする
        $company_id = mb_strlen(trim($data[0], '"')) <= self::COMPANY_ID ? 1 : 0;
        if (!$company_id) {
            return false;
        }
        $requests = array();
        switch ($prefix) {
            case 'agx':
            case 'agm':
                $bukken_type = mb_strlen(trim($data[1], '"')) <= self::BUKKEN_TYPE ? 1 : 0;
                $special_id = mb_strlen(trim($data[2], '"')) <= self::SPECIAL_ID ? 1 : 0;
                $mobile = mb_strlen(trim($data[3], '"')) <= self::MOBILE ? 1 : 0;
                array_push($requests, $bukken_type, $special_id, $mobile);
                break;
            case 'abk':
            case 'afd':
                $bukken_type = mb_strlen(trim($data[1], '"')) <= self::BUKKEN_TYPE ? 1 : 0;
                $bukken_number = mb_strlen(trim($data[2], '"')) <= self::BUKKEN_NUMBER ? 1 : 0;
                $niji_kokoku_jido_kokai = mb_strlen(trim($data[3], '"')) <= self::NIJI_KOKOKU_JIDO_KOKAI ? 1 : 0;
                $special_id = mb_strlen(trim($data[4], '"')) <= self::SPECIAL_ID ? 1 : 0;
                $recommend_bukken = mb_strlen(trim($data[5], '"')) <= self::RECOMMEND_BUKKEN ? 1 : 0;
                $map_serach = mb_strlen(trim($data[6], '"')) <= self::MAP_SEARCH ? 1 : 0;
                $mobile = mb_strlen(trim($data[7], '"')) <= self::MOBILE ? 1 : 0;
                array_push($requests, $bukken_type, $bukken_number, $niji_kokoku_jido_kokai, $special_id, $recommend_bukken, $map_serach, $mobile);
                break;
            case 'adp':
                $bukken_number = mb_strlen(trim($data[1], '"')) <= self::BUKKEN_NUMBER ? 1 : 0;
                $kaiin_number = mb_strlen(trim($data[2], '"')) <= self::KAIIN_NUMBER ? 1 : 0;
                $panorama_content = mb_strlen(trim($data[3], '"')) <= self::PANORAMA_CONTENT ? 1 : 0;
                $mobile = mb_strlen(trim($data[4], '"')) <= self::MOBILE ? 1 : 0;
                array_push($requests, $bukken_number, $kaiin_number, $panorama_content, $mobile);
                break;
            default:
                return false;
        }
        if (in_array(0, $requests, true)) {
            return false;
        }
        if (!$params->isPcMedia() && !$params->isSpMedia()) {
            return false;
        }
        return true;
    }

    private function fputcsv($file_name, $data)
    {
        $file = fopen($file_name, "a");
        if (!$file) {
            throw new \Exception("CLOGファイルのOPENに失敗しました。 file=$file_name  data=" . print_r($data, true));
        }
        foreach ($data as $v) {
            $tmp[] = $v;
        }
        $str = implode(',', $tmp) . "\n";
        if (fwrite($file, $str) < 1) {
            throw new \Exception("CLOGファイルのWRITEに失敗しました。 file=$file_name  data=" . print_r($data, true));
        }
        fclose($file);
    }

    private static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new CLogger();
        }
        return static::$instance;
    }

    // ph3で新しいファイルから新フォーマットにするため日付チェック
    private static function new_format()
    {
        $dt = new \DateTime();
        $dt->setTimeZone(new \DateTimeZone('Asia/Tokyo'));
        $current_time = $dt->format('Y-m-d H:i:s');
        if (strtotime($current_time) >= strtotime(static::$ph3_date)) {
            return true;
        }
        return false;
    }

    /*
	* 不正なリクエストを管理者あてにメール送信する
	*
	* @param array $apiData
	* @param array $companyObj
	*/
    protected function sendMail($companyObj)
    {
        $publishConfig = getConfigs('publish')->publish;
        $this->_envJp = $publishConfig->env_jp;
        $this->_mailFrom = $publishConfig->mail_from;
        $this->_mailTo = $publishConfig->mail_tos;
        $subject = '【' . $this->_envJp . '環境】不正なリクエストを検出しました';
        $body = '会員番号：' . $companyObj->member_no . "\n" . '会社名：' . $companyObj->company_name;
        try {
            // $mail = new Zend_Mail('ISO-2022-JP');
            $mail = new Mail();
            // $mail->setBodyText(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"), null, Zend_Mime::ENCODING_7BIT);
            $mail->setBody(mb_convert_encoding($body, "ISO-2022-JP", "UTF-8"));
            $mail->setFrom($this->_mailFrom);
            $mail->addTo($this->_mailTo);
            // $mail->setSubject(mb_convert_encoding($subject, "ISO-2022-JP", "UTF-8"));
            $mail->setSubject($subject);
            $mail->send();
        } catch (\Exception $e) {
            throw new \Exception('メール送信に失敗しました。');
        }
    }
}
