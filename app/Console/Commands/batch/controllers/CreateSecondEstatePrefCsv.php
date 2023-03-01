<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;

class CreateSecondEstatePrefCsv extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-second-estate-pref-csv {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create second estate pref csv';

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

                //オブジェクト取得
                $companyObj = App::make(CompanyRepositoryInterface::class);
    
                //$companyObj->setAutoLogicalDelete(false);//, 's.area_search_filter'
                $select = $companyObj->model()->withoutGlobalScopes()->select('member_no');
                $select->from("company as c");
                $select->join("second_estate as s",'c.id','=','s.company_id');
                $select->where("c.delete_flg",0);
                $select->where("c.contract_type",config('constants.company_agreement_type.CONTRACT_TYPE_PRIME'))
                ->orWhere("c.contract_type",config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'));
                $rows = $select->get();
                $rows_arr = $rows->toArray();
    
                //CSV準備
    
                // 出力ファイル名
                $fileName = "HPAD_SECOND_ESTATE.CSV";
    
                //CSVヘッダー情報
                $header_name = array(
                    '会員No',
                    '都道府県コード'
                );
    
                //CSV登録情報
                $data_key = array(
                    'member_no',
                    'area_search_filter'    
                );

                $stream = fopen($this->_path_data.$fileName, 'w');
                $csv_row_name = array();
                //ヘッダーの設定
                foreach($header_name as $name) {
                    mb_convert_variables('SJIS-win', 'UTF-8', $name);
                    $csv_row_name[] = $name;
                }
                
                $this->encfputscv($stream, $csv_row_name, ',', '"');
    
                //中身の設定
                foreach($rows_arr as $key => $val) {
                    $csv_row = array();
                    foreach($data_key as $name) {
                        $string = "";
                        $member_no = "";
                        if($name == 'member_no') {
                            $member_no=$val[$name];
                            $json = json_decode($val['area_search_filter'], true );
    
                            foreach ($json["area_1"]as $key => $value) {
                                $csv_row = array();
                                $string = $member_no;
                                mb_convert_variables('SJIS-win', 'UTF-8', $string);
                                $csv_row[] = (string)$string;
                                $string = $value;
                                mb_convert_variables('SJIS-win', 'UTF-8', $string);
                                $csv_row[] = (string)$string;
                                $this->encfputscv($stream, $csv_row, ',', '"');
                            }
                        }
                    }
                }
                fclose($stream);
    
            }catch(\Exception $e) {
                $this->_error->error($e);
                throw $e;
                exit;
            }
            $this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
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

// docker exec -it servi_80 bash 
// php artisan command:batch-create-second-estate-pref-csv development app CreateSecondEstatePrefCsv>> /var/www/html/storage/logs/CreateSecondEstatePrefCsv.log 2>&1
 