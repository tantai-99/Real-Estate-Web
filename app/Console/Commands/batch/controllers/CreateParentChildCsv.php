<?php
/**
 * 親子関係（グループ情報をCSVに吐き出す）
 *
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\AssociatedCompany\AssociatedCompanyRepositoryInterface;

class CreateParentChildCsv extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-parent-child-csv {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create parent child csv';

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

                $companyObj = App::make(CompanyRepositoryInterface::class);
                $acObj = App::make(AssociatedCompanyRepositoryInterface::class);
                // $acObj->setAutoLogicalDelete(false);
                $select = $acObj->getAssociatedCompany(array("ac.id","ac.parent_company_id","ac.subsidiary_company_id",
                    "(select c.member_no from company as c where c.id = ac.parent_company_id AND c.delete_flg = 0) as parent_no",
                    "(select c.member_no from company as c where c.id = ac.subsidiary_company_id AND c.delete_flg = 0) as child_no"
                ));

                //子会社がデモだったら、入れない対応
                $select->where("c.delete_flg", 0);
                $select->whereRaw("c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ." || c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'));
                //子会社がデモだったら、入れない対応

                $select->where("ac.delete_flg", 0);

                $rows = $acObj->fetchAll($select);

                //CSV準備
                // 出力ファイル名
                $fileName = "HPAD_ASSOCIATED_COMPANY.CSV";

                //CSVヘッダー情報
                $header_name = array(
                    '親会員No',
                    '子会員No'
                );

                //CSV登録情報
                $data_key = array(
                    'parent_no',
                    'child_no'
                );

                $stream = fopen($this->_path_data .$fileName, 'w');
                $csv_row_name = array();
                foreach($header_name as $name) {
                    mb_convert_variables('SJIS-win', 'UTF-8', $name);
                    $csv_row_name[] = $name;
                }
                $this->encfputscv($stream, $csv_row_name, ',', '"');

                //中身の設定
                foreach($rows as $key => $val) {

                    //子会社がデモだったら、入れない対応
                    $compnay_row = $companyObj->getCompanySelect($val->subsidiary_company_id);
                    if($compnay_row == NULL) continue;
                    //子会社がデモだったら、入れない対応

                    $csv_row = array();
                    foreach($data_key as $name) {
                        $string = (string)$val->$name;
                        mb_convert_variables('SJIS-win', 'UTF-8', $string);
                        $csv_row[] = (string)$string;
                    }

                    $as = $this->encfputscv($stream, $csv_row, ',', '"');
                }
                fclose($stream);
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
// php artisan command:batch-create-parent-child-csv development app CreateParentChildCsv >> /var/www/html/storage/logs/CreateParentChildCsv.log 2>&1