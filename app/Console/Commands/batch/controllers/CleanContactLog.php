<?php

/**
 * 2年以上前のお問い合わせログを削除する
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Repositories\ContactLog\ContactLogRepositoryInterface;
use App\Console\Commands\batch\BatchAbstract;

class CleanContactLog extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-clean-contact-log {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command clean contact log';

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
            $where = 'create_date <= (NOW() - INTERVAL 3 YEAR)';
            $table = App::make(ContactLogRepositoryInterface::class);
            DB::beginTransaction();

            try {
                $results = $table->getContactLog($where);
                foreach($results as $result) {
                    $result->forceDelete();
                }
                // $table->delete($where, true);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                throw $e;
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }
}
// php artisan command:batch-clean-contact-log development app CleanContactLog >> /var/www/html/storage/logs/CleanContactLog.log 2>&1