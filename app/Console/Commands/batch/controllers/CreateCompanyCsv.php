<?php
/**
 * 日時の契約店情報CSV作成送信バッチ
 *
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\LogDelete\LogDeleteRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepositoryInterface;
use Carbon\Carbon;

class CreateCompanyCsv extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-company-csv {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create company csv';

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
                //取得するもの
                $array = array(
                    'c.*',
                    's.applied_start_date as `s.applied_start_date`',
                    's.start_date as `s.start_date`',
                    's.contract_staff_id as `s.contract_staff_id`',
                    's.contract_staff_name as `s.contract_staff_name`',
                    's.contract_staff_department as `s.contract_staff_department`',
                    's.applied_end_date as `s.applied_end_date`',
                    's.end_date as `s.end_date`',
                    's.cancel_staff_id as `s.cancel_staff_id`',
                    's.cancel_staff_name as `s.cancel_staff_name`',
                    's.cancel_staff_department as `s.cancel_staff_department`',
                    's.company_id as `s.company_id`',
                    
                    // top original
                    'top.applied_start_date as `top.applied_start_date`',
                    'top.start_date as `top.start_date`',
                    'top.contract_staff_id as `top.contract_staff_id`',
                    'top.contract_staff_name as `top.contract_staff_name`',
                    'top.contract_staff_department as `top.contract_staff_department`',
                    'top.applied_end_date as `top.applied_end_date`',
                    'top.end_date as `top.end_date`',
                    'top.cancel_staff_id as `top.cancel_staff_id`',
                    'top.cancel_staff_name as `top.cancel_staff_name`',
                    'top.cancel_staff_department as `top.cancel_staff_department`',
                    // ATHOME_HP_DEV-4300: Add information FDP
                    'fdp.start_date as `fdp.start_date`',
                    'fdp.end_date as `fdp.end_date`',
                    );
                //オブジェクト取得
                $companyObj = App::make(CompanyRepositoryInterface::class);
                $assCompHpObj = App::make(AssociatedCompanyHpRepositoryInterface::class);
                $hpObj = App::make(HpPageRepositoryInterface::class);
                $logDelObj = App::make(LogDeleteRepositoryInterface::class);
                $esacObj = App::make(EstateAssociatedCompanyRepositoryInterface::class);
                $select = $companyObj->getCompanyCsv($array);
                $select->whereRaw("c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ." || c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'));
                $rows = $companyObj->fetchAll($select);
                $rows_arr = $rows->toArray();

                //グループ情報の取得（CSVにグループ系も入れるため）
                $acObj = App::make(AssociatedCompanyRepositoryInterface::class);
                $acRows = $acObj->fetchAll();
                $ac_datas = array();
                foreach($acRows as $key => $val) {
                    $member_no = "";
                    for($i=0; $i < count($rows_arr); $i++) {
                        if($rows_arr[$i]['id'] == $val->subsidiary_company_id) {
                            $member_no = $rows_arr[$i]['member_no'];
                        }
                    }
                    if($member_no != "") $ac_datas[$val->parent_company_id][] = $member_no;
                }
                
                //CSV準備

                // 出力ファイル名
                $fileName = "HPAD_COMPANY.CSV";
                //CSVヘッダー情報
                $header_name = array(
                    '会員No',
                    '加盟店ID',
                    '契約タイプ',
                    '会員名',
                    '会社名',
                    '利用ドメイン',
                    '利用開始申請日',
                    '利用開始日',
                    '契約担当者ID',
                    '契約担当者名',
                    '契約担当者部署',
                    '利用停止申請日',
                    '利用停止日',
                    '解約担当者ID',
                    '解約担当者名',
                    '解約担当者部署',
                    'FTP サーバー名',
                    'FTP ポート番号',
                    'FTP ユーザーID',
                    'FTP パスワード',
                    'FTP ディレクトリ名',
                    'FTP PASVモードフラグ',
                    'コンパネ アドレス',
                    'コンパネ ユーザーID',
                    'コンパネ パスワード',
                    'データ登録日',
                    'データ更新日',
                    '評価分析グループ設定の有無',
                    '評価分析親フラグ',
                    '2次広告自動公開オプション：利用開始申請日',
                    '2次広告自動公開オプション：利用開始日',
                    '2次広告自動公開オプション：契約担当者ID',
                    '2次広告自動公開オプション：契約担当者名',
                    '2次広告自動公開オプション：契約担当者部署',
                    '2次広告自動公開オプション：利用停止申請日',
                    '2次広告自動公開オプション：利用停止日',
                    '2次広告自動公開オプション：解約担当者ID',
                    '2次広告自動公開オプション：解約担当者名',
                    '2次広告自動公開オプション：解約担当者部署',
                    '物件グループオプション：グループ設定の有無',
                    '物件グループオプション：親フラグ',
                    '更新日',
                    '公開停止日',
                    '初回公開日',
                	'契約状況',
                	'プラン',
                	'予約_プラン',
                	'予約_利用開始申請日',
                	'予約_利用開始日',
                	'予約_契約担当者ID',
                	'予約_契約担当者名',
                	'予約_契約担当部署',
                	'初回利用開始日',
                	'地図_利用開始申請日',
                	'地図_利用開始日',
                	'地図_契約担当者ID',
                	'地図_契約担当者',
                	'地図_契約担当部署',
                	'地図_利用停止申請日',
                	'地図_利用停止日',
                	'地図_解約担当者ID',
                	'地図_解約担当者',
                	'地図_解約担当部署',
                  
                  // top original
                  'トップオリジナル_利用開始申請日',
                  'トップオリジナル_利用開始日',
                  'トップオリジナル_契約担当者ID',
                  'トップオリジナル_契約担当者',
                  'トップオリジナル_契約担当部署',
                  'トップオリジナル_利用停止申請日',
                  'トップオリジナル_利用停止日',
                  'トップオリジナル_解約担当者ID',
                  'トップオリジナル_解約担当者',
                  'トップオリジナル_解約担当部署',
                    // ATHOME_HP_DEV-4300: Add information FDP
                    'FDP_利用開始日',
                    'FDP_利用停止日',
                );

                //CSV登録情報
                $data_key = array(
                    'member_no',
                    'id',
                    'contract_type',
                    'member_name',
                    'company_name',
                    'domain',
                    'applied_start_date',
                    'start_date',
                    'contract_staff_id',
                    'contract_staff_name',
                    'contract_staff_department',
                    'applied_end_date',
                    'end_date',
                    'cancel_staff_id',
                    'cancel_staff_name',
                    'cancel_staff_department',
                    'ftp_server_name',
                    'ftp_server_port',
                    'ftp_user_id',
                    'ftp_password',
                    'ftp_directory',
                    'ftp_pasv_flg',
                    'cp_url',
                    'cp_user_id',
                    'cp_password',
                    'create_date',
                    'update_date',
                    'in_group',
                    'parent_flg',
                    's.applied_start_date',
                    's.start_date',
                    's.contract_staff_id',
                    's.contract_staff_name',
                    's.contract_staff_department',
                    's.applied_end_date',
                    's.end_date',
                    's.cancel_staff_id',
                    's.cancel_staff_name',
                    's.cancel_staff_department',
                    'bukken_group',
                    'bukken_parent',
                    'release_date',
                    'published_stop_date',
                    'first_publish_date',
                	'contract_status',
                	'cms_plan',
                	'reserve_cms_plan',
                	'reserve_applied_start_date',
                	'reserve_start_date',
                	'reserve_contract_staff_id',
                	'reserve_contract_staff_name',
                	'reserve_contract_staff_department',
                	'initial_start_date',
                	'map_applied_start_date',
                	'map_start_date',
                	'map_contract_staff_id',
                	'map_contract_staff_name',
                	'map_contract_staff_department',
                	'map_applied_end_date',
                	'map_end_date',
                	'map_cancel_staff_id',
                	'map_cancel_staff_name',
                	'map_cancel_staff_department',
                  
                  // top original
                  'top.applied_start_date',
                  'top.start_date',
                  'top.contract_staff_id',
                  'top.contract_staff_name',
                  'top.contract_staff_department',
                  'top.applied_end_date',
                  'top.end_date',
                  'top.cancel_staff_id',
                  'top.cancel_staff_name',
                  'top.cancel_staff_department',
                    // ATHOME_HP_DEV-4300: Add information FDP
                    'fdp.start_date',
                    'fdp.end_date',
                );

                $stream = fopen($this->_path_data . $fileName, 'w');
                $csv_row_name = array();
                foreach($header_name as $name) {
                    mb_convert_variables('SJIS-win', 'UTF-8', $name);
                    $csv_row_name[] = $name;
                }
                $this->encfputscv($stream, $csv_row_name, ',', '"');

                $csvs = array();
                
                //中身の設定
                foreach($rows_arr as $key => $val) {
                    $csv_row = array();

                    //HP_PAGEの状況を確認する
                    $assCompHpRow = $assCompHpObj->fetchRowByCompanyId($val['id']);

                    foreach($data_key as $name) {

                        $string = "";

                        //子会社設定の有無
                        if($name == "in_group") {

                            //自分が親で子供がいるか
                            $child_cnt = $acObj->getChildrenCountForCompanyId($val['id']);

                            //自分が子供で親がいるか
                            $parent_cnt = $acObj->getParentCountForCompanyId($val['id']);

                            $in_group = 0;
                            if(((int)$child_cnt->cnt + (int)$parent_cnt->cnt) > 0) $in_group = 1;
                            $string = (string)$in_group;

                        //親フラグ
                        }else if($name == "parent_flg") {
                            $parent_flg = 0;
                            if(isset($ac_datas[$val['id']]) && count($ac_datas[$val['id']]) > 0) $parent_flg = 1;
                            $string = (string)$parent_flg;

                        //パスワードは平文にする
                        }else if($name == "ftp_password" || $name == "cp_password") {
                            $string = (string)$rows[$key]->$name;

                        //最終更新日を設定
                        }else if($name == "release_date") {

                            //まだ作成していない
                            if($assCompHpRow != null) {
                                // $select = $hpObj->select();
                                // $select->where("hp_id = ?", $assCompHpRow->current_hp_id);
                                // $select->where("public_flg = 1");
                                // $select->order("published_at DESC");
                                $where = array(
                                    ['hp_id', $assCompHpRow->current_hp_id],
                                    ['public_flg', 1]
                                );
                                $order = array('DESC' => 'published_at');
                                $pubRow = $hpObj->fetchRow($where, $order);
                                if($pubRow != null) $string = $pubRow->published_at;
                            }

                        //最終更新停止日を設定
                        }else if($name == "published_stop_date") {
                            $delRow = $logDelObj->getLastDeleteForComapnyId($val['id']);
                            if($delRow != null) $string = $delRow->datetime;

                        }else if($name == "bukken_group") {//親会社または子会社があるか
                            $string = 0;
                            $childCnt=$esacObj->getChildrenCountByCompanyId($val['id'])->cnt;//子供の数
                            $parentCnt=$esacObj->getParentCountByCompanyId((int)$val['member_no'])->cnt;//親の数

                            if($childCnt > 0 || $parentCnt > 0){//どちらかあれば
                                $string = 1;
                            } 
                        }else if($name == "bukken_parent") { //親会社であるか
                            $string = 0;
                            $childCnt=$esacObj->getChildrenCountByCompanyId($val['id'])->cnt;//子供の数
                            if($childCnt > 0 ){ //子があれば
                                $string = 1;
                            } 
                        } else if ( $name == 'contract_status' )
                        {
                        	$value = $rows[ $key ]->isAvailable()																?  1	: 2			;
                        	$value = $rows[ $key ]->initial_start_date															? $value: ''		;
                        	$value = $rows[ $key ]->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE')	? '9'	: $value	;
                        	$string = (string)$value	;
                        } else if ( ( $name == "cms_plan" ) || ( $name == "reserve_cms_plan" ) )
                        {
                        	$string = (string)$this->getPlanVal( $val[ $name ] )	;
                        }else{
                            if (strpos($name, 'fdp.') === 0 && (substr($val[$name], 0, 10) == "0000-00-00")) {
                                $string = '';
                            } else {
                                $string = (string)$val[$name];
                            }
                        }

                        mb_convert_variables('SJIS-win', 'UTF-8', $string);
                        $csv_row[] = (string)$string;
                    }

                    $this->encfputscv($stream, $csv_row, ',', '"');

                }
                fclose($stream);
                // ATHOME_HP_DEV-5438 終了済みを示すファイルを生成する
                $baseDay = Carbon::now();
                $prefix = $baseDay->format('Ymd');
                $doneFileName = $prefix . '_HPAD_COMPANY_DONE.txt';
                touch($this->_path_data . $doneFileName);
                // ATHOME_HP_DEV-5438 前日分を削除する
                $targetDay = $baseDay->subDay()->format('Ymd');
                $targetFileName = $targetDay . '_HPAD_COMPANY_DONE.txt';
                $targetFile = $this->_path_data . $targetFileName;
                if (file_exists($targetFile)) {
                    unlink($targetFile);
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
    
    private function getPlanVal( $val )
    {
    	$result		= ''	;
    	switch ( $val )
    	{
    		case config('constants.cms_plan.CMS_PLAN_ADVANCE')	: $result =	1 ; break ;
    		case config('constants.cms_plan.CMS_PLAN_STANDARD')	: $result =	2 ; break ;
    		case config('constants.cms_plan.CMS_PLAN_LITE')	    : $result =	3 ; break ;
    	}
    	
    	return  $result	;
    }

    private function encfputscv($fp, $row, $delimiter = ',', $enclosure = '"', $eol = "\n"){
        $tmp = array();
        foreach($row as $v){
            $v = str_replace('"', '""', $v);
            $tmp[]= $enclosure.$v.$enclosure;
        }
        $str = implode($delimiter, $tmp).$eol;
        return fwrite($fp, $str);
    }

}
// php artisan command:batch-create-company-csv development app CreateCompanyCsv >> /var/www/html/storage/logs/CreateCompanyCsv.log 2>&1