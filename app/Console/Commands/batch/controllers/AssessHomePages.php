<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use Illuminate\Support\Facades\DB;
use App\Repositories\Hp\HpRepositoryInterface;
use Library\Custom\Assessment;

class AssessHomePages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-assess-home-pages {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command assess home pages';

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

            $this->validateArguments($args);

            $target_date = date('Y-m-d', strtotime('-1 day'));
            $target_hp_id = isset($args[1]) ? $args[1] : null;

            $this->_info->info('start assessment for ' . ($target_hp_id ? $target_hp_id : 'all'));

            $hpTable = App::make(HpRepositoryInterface::class);
            $select = null;
            
            DB::beginTransaction();
            try {
                if ($target_hp_id) {
                    $select = array(['id', $target_hp_id]);
                }
                $hpRowset = $hpTable->fetchAll($select);

                foreach ($hpRowset as $hp) {
                    $this->_info->info('> current target = ' . $hp->id);

                    $assessment = new Assessment($hp);
                    $assessment->assess();
                    $assessment->saveAs($target_date);
                    $this->_info->info('> done '. $hp->id);
                }

                DB::commit();
                $this->_info->info('done all.');
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    private function validateArguments(array $args)
    {
        if (count($args) < 2) {
            return true;
        }

        $target_hp = $args[1];
        if (isset($target_hp) && (!is_numeric($target_hp) || $target_hp < 1)) {
            throw new InvalidArgumentException('invalid hp_id args[2] (' . $target_hp . ') ');
        }

        return true;
    }

}
// php artisan command:batch-assess-home-pages development app AssessHomePages >> /var/www/html/storage/logs/AssessHomePages.log 2>&1