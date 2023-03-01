<?php
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use Illuminate\Support\Facades\DB;
use App\Repositories\OriginalSetting\OriginalSettingRepositoryInterface;
use Library\Custom\Model\Lists\Original;

class TopOriginalEvent extends Command
{
    /**
     * @param array $args
     * @throws Exception
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-top-original-event {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command top original event';

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
            $args = array_slice($arguments, 3);

            $this->_info->info('/////////////// START ///////////////');

            error_reporting(E_ALL ^ E_WARNING);

            // ATHOME_HP_DEV-4448: Change date run batch Top original
            $target_date = isset($args[1]) ? $args[1] : date('Y-m-d');

            /** @var Zend_Db_Table_Abstract $companyTable */
            $originalTable = App::make(OriginalSettingRepositoryInterface::class);

        	// get all companies have settings start_date yesterday | TURN ON TOP
            // and still not yet expired
            $newSelect = $originalTable->getSelectTop($target_date);
            $settings = $originalTable->fetchWithCompany($newSelect);

            if($settings && !empty($settings)){
                foreach($settings as $v){
                    /** @var App\Repositories\OriginalSetting\OriginalSettingRepository $setting */
                    $setting = $v['data'];
                    /** @var App\Models\Company $company */
                    $company = $v['company'];
                    if (!$company) continue;
                    // only execute if company plan support top original
                    if(!Original::checkPlanCanUseTopOriginal($company->cms_plan)) continue;
                    $this->_info->info('Up Option Top Original for Company ID: ' . $company->id . ' : Running...');
                    DB::beginTransaction();
                    try {
                        // turn on
                        Original::callTopOriginalEvent($company, true, false);
                        $this->_info->info('Up Option Top Original for Company ID: ' . $company->id . ' : Success');
                        DB::commit();
                    }
                    catch (\Exception $e) {
                        DB::rollBack();
                        $this->_error->error('Up Option Top Original for Company ID: ' . $company->id . ' : Failed');
                        $this->_error->error($e->getMessage());
                    }
                }
            }

            // end top
            // get all companies have settings end_date yesterday => DOWN TOP
            $where = array(
                ['end_date', '<=', $target_date],
                ['all_update_top', 1]
            );
            $expiredSettings = $originalTable->fetchWithCompany($where);

            if($expiredSettings && !empty($expiredSettings)){
                foreach($expiredSettings as $v){
                    $setting = $v['data'];
                    /** @var App\Repositories\Company\CompanyRepository $company */
                    $company = $v['company'];
                    if(!$company) continue;
                    $this->_info->info('Down Option Top Original for Company ID: '.$company->id . ' : Running...');
                    DB::beginTransaction();
                    try{
                        $topBefore = $company->checkTopOriginal();
                        // turn off
                        Original::callTopOriginalEvent($company,false, $topBefore);
                        DB::commit();
                        $this->_info->info('Down Option Top Original for Company ID: '.$company->id . ' : Success');
                    }
                    catch(\Exception $e){
                        DB::rollBack();
                        $this->_error->error('Down Option Top Original for Company ID: '.$company->id . ' : Failed');
                        $this->_error->error($e->getMessage());
                    }
                }
            }

            $this->_info->info('done all.');

            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }
}
// php artisan command:batch-top-original-event development app TopOriginalEvent >> /var/www/html/storage/logs/TopOriginalEvent.log 2>&1