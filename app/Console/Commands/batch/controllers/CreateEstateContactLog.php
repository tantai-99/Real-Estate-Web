<?php
/**
*物件お問い合わせログ
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\EstateContactCount\EstateContactCountRepositoryInterface;
use DateTime;
use DateTimeZone;

class CreateEstateContactLog extends Command {

    const TRACE_BACK_DATE = 5; //何日分遡るか
    //   12/15ログから対象（なので問い合わせログは12/16の夜間バッチの出力から）
    private static $ph3_date = '2016/12/16 00:00:00';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-estate-contact-log {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create estate contact log';

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
                $contactcountObj = App::make(EstateContactCountRepositoryInterface::class);
                // $contactcountObj->setAutoLogicalDelete(false);

                $fileName = "mla".date("Ymd", strtotime("-1 day")).".txt";
                $this->makeFile($fileName,$contactcountObj->getPerDayData());//本日の分の生成

                //日付を遡って無いものは補完する(本日生成する分の日付はのぞく)
                for ($i=2; $i<=self::TRACE_BACK_DATE+1; $i++) {
                    $fileName = storage_path('logs/inquiry_log/mla') . date("Ymd", strtotime("-".$i." day")).".txt";
                    if(!file_exists($fileName)) {
                        $fileName = "mla".date("Ymd", strtotime("-".$i." day")).".txt";//ファイル名
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

    private function makeFile($fileName,$data) {
        $header_name = array(
            'ID',   //問い合わせid
            '日付',
            '時刻',
            '加盟店ID',
            'お問い合わせ種別', //04:賃居 05:賃事 06:売居 07:売事
            '物件番号',
            '2次広告自動公開フラグ',
            '特集ID',
            'おすすめ物件フラグ', //0:非おすすめ物件 1:おすすめ物件
            '地図検索遷移フラグ', //0:非地図検索、1:地図検索
            'PC/モバイル区分', //pc:00 sp:02
            'ユーザーIP',
            'ユーザーエージェント',
            '物件ID',
            '物件バージョン番号',
            '周辺エリア情報', // 4293: Add FDP contact log
        );
        //CSV登録情報
        $data_key = array(
            'id',
            'date',
            'time',
            'company_id',
            'contact_type',
            'estate_number',
            'second_estate_flg',
            'special_id',
            'recommend_flg',
            'from_searchmap',
            'pc_or_sp',
            'user_ip',
            'user_agent',
            'bukken_id',
            'version_no',
            'peripheral_flg', // 4293: Add FDP contact log
        );

        // 旧フォーマット
        if (!static::new_format()) {
            $header_name = array(
                'ID',   //問い合わせid
                '日付',
                '時刻',
                '加盟店ID',
                'お問い合わせ種別', //04:賃居 05:賃事 06:売居 07:売事
                '物件番号',
                '2次広告自動公開フラグ',
                '特集ID',
                'おすすめ物件フラグ', //0:非おすすめ物件 1:おすすめ物件
                //'地図検索遷移フラグ', //0:非地図検索、1:地図検索
                'PC/モバイル区分', //pc:00 sp:02
                //'ユーザーIP',
                'ユーザーエージェント',
                '周辺エリア情報', // 4293: Add FDP contact log
            );
            //CSV登録情報
            $data_key = array(
                'id',
                'date',
                'time',
                'company_id',
                'contact_type',
                'estate_number',
                'second_estate_flg',
                'special_id',
                'recommend_flg',
                //'from_searchmap',
                'pc_or_sp',
                //'user_ip',
                'user_agent',
                'peripheral_flg', // 4293: Add FDP contact log
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
                        $type = '';
                        if ($page_code == '44') { // 賃貸
                            $type = '04';
                        } else if ($page_code == '45') { // 事賃
                            $type = '05';
                        } else if ($page_code == '46') { // 居売
                            $type = '06';
                        } else {    // 事売
                            $type = '07';
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
            if( $k === 'id' || $k === 'company_id' || $k === 'special_id') {
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
// php artisan command:batch-create-estate-contact-log development app CreateEstateContactLog >> /var/www/html/storage/logs/CreateEstateContactLog.log 2>&1