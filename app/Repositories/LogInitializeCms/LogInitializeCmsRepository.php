<?php
namespace App\Repositories\LogInitializeCms;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\LogInitializeCms;

use function Symfony\Component\Translation\t;

class LogInitializeCmsRepository extends BaseRepository implements LogInitializeCmsRepositoryInterface {
    
	public function getModel()
    {
        return \App\Models\LogInitializeCms::class;
    }
}