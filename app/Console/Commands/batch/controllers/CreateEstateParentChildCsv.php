<?php
/**
 *物件グループ会員情報CSVフォーマット(KAI-ATHP連携用)
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;

class CreateEstateParentChildCsv extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-create-estate-parent-child-csv {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command create estate parent child csv';

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
                $estateObj = App::make(EstateAssociatedCompanyRepositoryInterface::class);
                // $estateObj->setAutoLogicalDelete(false);
                $select = $estateObj->getEstateAssociatedCompany(array("es.parent_company_id","es.subsidiary_member_no as child_no",
                    "(select c.member_no from company as c where c.id = es.parent_company_id AND c.delete_flg = 0) as parent_no"));

                //子会社がデモだったら、入れない対応
                $select->where("c.delete_flg", 0);
                $select->whereRaw("c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_PRIME') ." || c.contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'));
                $select->where("es.delete_flg", 0);
                $rows = $estateObj->fetchAll($select);

                //CSV準備
                // 出力ファイル名
                $fileName = "HPAD_ESTATE_ASSOCIATED_COMPANY.CSV";

                $header_name = array(
                    '親会員No',
                    '子会員No'
                );
                //CSV登録情報
                $data_key = array(
                    'parent_no',
                    'child_no'
                );
                
                $stream = fopen($this->_path_data . $fileName, 'w');
                $csv_row_name = array();
                foreach($header_name as $name) {
                    mb_convert_variables('SJIS-win', 'UTF-8', $name);
                    $csv_row_name[] = $name;
                }
                $this->encfputscv($stream, $csv_row_name, ',', '"');

                //中身の設定
                foreach($rows as $key => $val) {
                    //子会社がデモだったら、入れない対応
                    // $company_select = $companyObj->select();
                    // $company_select->where("member_no = ?", $val->child_no);
                    // $company_select->where("delete_flg = ?", 0);
                    // $company_select->where("contract_type = ". config('constants.company_agreement_type.CONTRACT_TYPE_DEMO'));
                    $where = array(
                        ['member_no', $val->child_no],
                        ['delete_flg', 0],
                        ['contract_type', config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')],
                    );
                    $compnay_row = $companyObj->fetchRow($where);
                    if($compnay_row != NULL) continue;
                    //子会社がデモだったら、入れない対応

                    $csv_row = array();
                    foreach($data_key as $name) {
                        $string = (string)$val->$name;
                        mb_convert_variables('SJIS-win', 'UTF-8', $string);
                        $csv_row[] = (string)$string;
                    }

                    $this->encfputscv($stream, $csv_row, ',', '"');
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
// php artisan command:batch-create-estate-parent-child-csv development app CreateEstateParentChildCsv >> /var/www/html/storage/logs/CreateEstateParentChildCsv.log 2>&1