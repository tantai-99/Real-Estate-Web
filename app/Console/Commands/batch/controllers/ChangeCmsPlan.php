<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use Library\Custom\Plan\ChangeCms;
use Library\Custom\Model\Lists\CmsPlan;
use App\Console\Commands\batch\BatchAbstract;

class ChangeCmsPlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-change-cms-plan {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command change cms plan';

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
            // 変更対象のホームページを取得
            $where  = array (
                ['reserve_start_date', '!=', "''"],
                ['reserve_start_date', '<=', 'now()'],
            ) ;
            $commany    = App::make(CompanyRepositoryInterface::class);
            $rowset     = $commany->fetchAll($where);

            $table      = App::make(AssociatedCompanyHpRepositoryInterface::class);;
            $changer    = new ChangeCms();
            
            foreach ($rowset as $row)
            {
                if ($row->contract_type != config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'))
                {   // 「評価・分析のみ契約」以外は、プランのチェックを行う
                    if (CmsPlan::getCmsPLanName($row->reserve_cms_plan) == 'unknown')
                    {
                        $this->_info->info('指定されたプランが存在しない為、スキップ。' );
                        continue;
                    }
                }
                $associatedRow  = $table->fetchRow(array(['company_id', $row->id]));
                if ($associatedRow && $associatedRow->current_hp_id)
                {
                    $changer->changePlan($associatedRow->current_hp_id, $row);
                }
                else
                {   // HPがまだ無い時は、情報だけを変更。HPの作成は初回ログイン時となる。
                    $changer->updatePlanInfo($row, false);
                }
                
                if ($row->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE'))
                {
                    $this->_info->info("{$row->member_no}({$row->member_name})を「利用中」に変更完了");
                }
                else
                {
                    $planName = (new CmsPlan())->getCmsPLanNameByList($row->cms_plan);
                    $this->_info->info("{$row->member_no}({$row->member_name})を「{$planName}」に変更完了");
                }
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }
}
// php artisan command:batch-change-cms-plan development app ChangeCmsPlan >> /var/www/html/storage/logs/ChangeCmsPlan.log 2>&1