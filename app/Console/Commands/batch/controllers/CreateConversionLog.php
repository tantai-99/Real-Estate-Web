<?php
/**
 *  コンバージョンログCSVを作成する
 * ・バッチ起動日の前日の
 * 　　例：2017年4月10日にバッチ起動した場合、2017年4月9日を対象としたログファイルを作成する
 *
 * ・当日から遡って５日間はリカバリ対象とする。（作成されていなかったら場合に作成する）
 *
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Conversion\ConversionRepositoryInterface;

class CreateConversionLog extends Command {

    /* 指定がある場合は、指定日のみ対象にCSVを作成します。
     *   例：DBG_TARGET_DAY = '2017-04-13';
     * 指定がない場合は下記の設定にしてください
     *   例：DBG_TARGET_DAY = '';
     */
    const DBG_TARGET_DAY = '';
    //const DBG_TARGET_DAY = '2017-04-13';


    const RECOVERY_DAYS = 4; //何日分遡るか

    const FILENAME_PREFIX_TELTAP = "conversion_teltap_log"; // conversion_teltap_log_yyyymmdd.csv

    /**
     * @var App\Repositories\Conversion\ConversionRepositoryInterface:
     */
    private $conversionObj;

    /**
     * @var array 電話番号タップコンバージョンCSVファイルのヘッダ
     */
    private $telCsvHeader;

    /**
     * @var array 電話番号タップコンバージョンCSVファイル用のデータキー
     */
    private $telCsvDataKey;


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-conversion-log {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create conversion log';

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

                //CSV出力ディレクトリ
                $this->csvDir = 's3://'.env('AWS_BUCKET') . 'logs/conversion_log';

                $baseDay  = Carbon::now();

                //バッチ実施日の1日前を対象日にする
                $targetDay = $baseDay->subDay();

                // 対象日より前の４日間をリカバリー日にする
                $recoveryDay = $targetDay->subDays(self::RECOVERY_DAYS);
                $recoveryDays = [];
                for ($i=0; $i<self::RECOVERY_DAYS; $i++) {
                    $recoveryDays[$i] = clone $recoveryDay;
                    $recoveryDay = $recoveryDay->addDay();
                }

                // コンバージョンテーブル
                $this->conversionObj = App::make(ConversionRepositoryInterface::class);

                // ターゲットに指定がある場合は指定日だけを対象にする
                if(self::DBG_TARGET_DAY){
                    $targetDay = new Carbon(self::DBG_TARGET_DAY);
                    $targetDay->format('Ymd');
                    $recoveryDays = [];

                }

                //CSV作成
                $this->createTelConversionCsvFiles($targetDay,$recoveryDays);


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

    /**
     * @param $targetDay
     * @param $recoveryDays
     */
    private function createTelConversionCsvFiles($targetDay,$recoveryDays){

        // ターゲット
        $recoveryFlg = false;
        $this->createTelConversionCsvFile($targetDay,$recoveryFlg);

        // リカバリ
        $recoveryFlg = true;
        foreach($recoveryDays as $recoveryDay){
            $str = $recoveryDay->format('Ymd');
            $this->createTelConversionCsvFile($recoveryDay,$recoveryFlg);
        }
    }


    private function createTelConversionCsvFile($day,$recoveryFlg){

        // CSVヘッダ名
        $csvHeader = array(
            '日付',
            '時刻',
            '加盟店ID',
            '会員No',
            'ページURL',
            'PC/モバイル区分', //pc:00 sp:02
            'ユーザーIP',
            'ユーザエージェント',
        );

        $header = array();
        foreach($csvHeader as $name) {
            mb_convert_variables('SJIS-win', 'UTF-8', $name);
            $header[] = $name;
        }
        $csvHeader = $header;

        mb_convert_variables('SJIS-win', 'UTF-8', $name);


        // ファイル名
        $fileName = self::FILENAME_PREFIX_TELTAP.'_'.$day->format('Ymd').'.csv';
        $filePath = $this->csvDir.'/'.$fileName;

        // リカバリの場合はファイルがない場合のみ再出力する
        if($recoveryFlg && file_exists($filePath)){
            return;
        }

        // CSVファイル
        $stream = fopen($filePath , 'w');
        $this->encfputscv($stream, $csvHeader, ',', '"');

        // 電話番号リンクタップのコンバージョンを取得する
        $rowSet = $this->conversionObj->getTeltap($day);

        foreach($rowSet as $row){

            $data = [];
            $data['date']         = (string)date("Ymd",strtotime($row->recieve_date));
            $data['time']         = (string)date("His",strtotime($row->recieve_date));
            $data['company_id']   = (int)$row->company_id;
            $data['member_no']    = $row->member_no;
            $data['page_url']     = $row->page_url;
            $data['device']       = ($row->device==1) ? '00' : '02' ;
            $data['user_ip']      = $row->user_ip;
            $data['user_agent']   = $row->user_agent;

            $this->encfputscv($stream, $data, ',', '"');

        }
    }


    // コンバージョン用CSVファイル出力用のディレクトリを作成する
    private function createCsvDir(){

        // なければ作る
        if (!file_exists($this->csvDir)) {
            // ディレクトリ作成
            if (!mkdir($this->csvDir, 0777, true)) {
                throw new Exception('コンバージョンログのディレクトリの作成に失敗しました。(' . $this->csvDir . ')');
            }
        }
    }

    // scv-put
    private function encfputscv($fp, $row, $delimiter = ',', $enclosure = '"', $eol = "\n"){
        $tmp = array();
        foreach($row as $k => $v){
            $tmp[]= $enclosure.$v.$enclosure;
        }
        $str = implode($delimiter, $tmp).$eol;
        return fwrite($fp, $str);
    }

}
// php artisan command:batch-create-conversion-log development app CreateConversionLog >> /var/www/html/storage/logs/CreateConversionLog.log 2>&1
