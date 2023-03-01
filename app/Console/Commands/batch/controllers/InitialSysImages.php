<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpImage\HpImageRepositoryInterface;

class InitialSysImages extends Command{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'command:batch-initial-sys-images {env?} {app?} {controller?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command initial sys images';
 
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
			$hpTable = App::make(HpRepositoryInterface::class);
			$hpIds = array();
			foreach ($hpTable->fetchAll() as $hp) {
				$hpIds[] = $hp->id;
			}
			
			$hpImageTable = App::make(HpImageRepositoryInterface::class);
			$hpImageTable->initSysImages($hpIds);
			$this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
    }
}

// docker exec -it servi_80 bash 
// php artisan command:batch-initial-sys-images development app InitialSysImages>> /var/www/html/storage/logs/InitialSysImages.log 2>&1
//