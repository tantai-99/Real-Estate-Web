<?php
/**
 *会社お問い合わせログ
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use DateTime;
use DateTimeZone;
use App\Repositories\ContactCount\ContactCountRepositoryInterface;
use App\Console\Commands\batch\BatchAbstract;

class CreateCompanyContactLog extends Command {

    const TRACE_BACK_DATE = 5; //何日分遡るか

    // ph3で新フォーマットにする日付
    //   12/15ログから対象（なので問い合わせログは12/16の夜間バッチの出力から）
    private static $ph3_date = '2016/12/16 00:00:00';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-company-contact-log {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create company contact log';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
    try {
            $arguments = $this->arguments();
            BatchAbstract::validParamater($arguments, $this);

            $this->_info->info('/////////////// START ///////////////');
            try {
                $contactcountObj = App::make(ContactCountRepositoryInterface::class);
                // $contactcountObj->setAutoLogicalDelete(false);

                $fileName = "mka".date("Ymd", strtotime("-1 day")).".txt";
                $this->makeFile($fileName,$contactcountObj->getPerDayData());//本日の分の生成

                //日付を遡って無いものは補完する(本日生成する分の日付はのぞく)
                for ($i=2; $i<=self::TRACE_BACK_DATE+1; $i++) {
                    $fileName = storage_path('logs/inquiry_log/mka').date("Ymd", strtotime("-".$i." day")).".txt";
                    if(!file_exists($fileName)) {
                        $fileName = "mka".date("Ymd", strtotime("-".$i." day")).".txt";//ファイル名
                        $this->makeFile($fileName,$contactcountObj->getPerDayData("-".$i));
                    }
                }


            }catch(\Exception $e) {
                $this->_error->error($e);
                throw $e;
                exit;
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    private function makeFile($fileName,$data){
        $header_name = array(
            'ID',   //問い合わせid
            '日付',
            '時刻',
            '加盟店ID',
            'お問い合わせ種別',
            'PC/モバイル区分',
            'ユーザーIP',
            'ユーザーエージェント',
        );
        //CSV登録情報
        $data_key = array(
            'id',
            'date',
            'time',
            'company_id',
            'contact_type', //01:会社お問い合わせ 02:資料請求 03:査定依頼
            'pc_or_sp', //pc:00 sp:02
            'user_ip',
            'user_agent',
        );

        // ph3フォーマット適応前
        if (!static::new_format()) {
            $header_name = array(
                'ID',   //問い合わせid
                '日付',
                '時刻',
                '加盟店ID',
                'お問い合わせ種別',
                'PC/モバイル区分',
                //'ユーザーIP',
                'ユーザーエージェント',
            );
            //CSV登録情報
            $data_key = array(
                'id',
                'date',
                'time',
                'company_id',
                'contact_type', //01:会社お問い合わせ 02:資料請求 03:査定依頼
                'pc_or_sp', //pc:00 sp:02
                //'user_ip',
                'user_agent',
            );
        }

        $stream = fopen(storage_path('logs/inquiry_log/') . $fileName, 'w');
        $csv_row_name = array();
        foreach($header_name as $name) {
            $csv_row_name[] = $name;
        }
        //$this->encfputscv($stream, $csv_row_name, ',', '"');

        //中身の設定
        foreach($data as $key => $val) {

            $csv_row = array();
            foreach($data_key as $name) {
                switch ($name) {
                    case 'date':
                        $string = (string)date("Ymd",strtotime($val->recieve_date));
                        break;
                    case 'time':
                        $string = (string)date("His",strtotime($val->create_date));
                        break;
                    case 'contact_type':
                        $page_code = $val->page_type_code;
                        $type = '01';//会社問い合わせ

                        // 資料請求
                        if ($page_code == '42') {
                            $type = '02';
                        }
                        // 査定依頼
                        else if ($page_code == '43') {
                            $type = '03';
                        }
                        // 物件リクエスト(居住用賃貸物件フォーム)
                        else if ($page_code == '50') {
                            $type = '08';
                        }
                        // 物件リクエスト(事務所用賃貸物件フォーム)
                        else if ($page_code == '51') {
                            $type = '09';
                        }
                        // 物件リクエスト(居住用売買物件フォーム)
                        else if ($page_code == '52') {
                            $type = '10';
                        }
                        // 物件リクエスト(事務所用売買物件フォーム)
                        else if ($page_code == '53') {
                            $type = '11';
                        }

                        $string = (string)$type;
                        break;
                    case 'pc_or_sp':
                        $device = '00'; //pc
                        if($val->device == '2') {//sp
                            $device = '02';
                        }
                        $string = (string)$device;
                        break;
                    default:
                        $string = (string)$val->$name;
                        break;
                }
                $csv_row[$name] = (string)$string;
            }

            $this->encfputscv($stream, $csv_row, ',', '"');
        }
        fclose($stream);

    }

    private function encfputscv($fp, $row, $delimiter = ',', $enclosure = '"', $eol = "\n"){
        $tmp = array();
        foreach($row as $k => $v){
            $v = str_replace('"', '""', $v);
            if($k === 'id' || $k === 'company_id') {
                $tmp[]= $v;
            }else{
                $tmp[]= $enclosure.$v.$enclosure;
            }
        }
        $str = implode($delimiter, $tmp).$eol;
        return fwrite($fp, $str);
    }


    // ph3で新しいファイルから新フォーマットにするため日付チェック
    private static function new_format(){
        $dt = new DateTime();
        $dt->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $current_time = $dt->format('Y-m-d H:i:s');
        if (strtotime($current_time) >= strtotime(static::$ph3_date)) {
            return true;
        }
        return false;
    }
}
// php artisan command:batch-create-company-contact-log development app CreateCompanyContactLog >> /var/www/html/storage/logs/CreateCompanyContactLog.log 2>&1