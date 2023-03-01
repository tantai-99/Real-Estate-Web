<?php

namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\Manager\ManagerRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\CompanyAccount\CompanyAccountRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InitialSetting extends Command {
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'command:batch-initial-setting {env?} {app?} {controller?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command initial setting';
 
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
				$table = App::make(ManagerRepositoryInterface::class);

				//$adapter = $table->getAdapter();
				DB::beginTransaction();

				//初期管理者を作成する

				$row = $table->fetchRow(array(["id",1]));
				
				if($row == NULL) {
					$row = $table->create();
					$row->name = '管理者';
					$row->login_id = 'admin';
					$row->password = 'admin';
					$row->privilege_edit_flg   = 1;
					$row->privilege_manage_flg = 1;
					$row->privilege_create_flg = 1;
					$row->privilege_open_flg   = 1;
					$row->save();
				}

				//本番以外であれば、デモ用の会社も作成する
				//if(APPLICATION_ENV != "production") {
				//加盟店アカウント作成はOFF にしておきます
				if(0) { 

					$now = date('Y-m-d H:i:s');
					
					$table = App::make(CompanyRepositoryInterface::class);
					$member_no = $this->createMemberNo();
					

					//加盟店の作成
					$row = $table->create(array(
						'contract_type' => config('constants.company_agreement_type.CONTRACT_TYPE_DEMO'),
						'member_no'     => $member_no,
						'member_name'   => "アットホーム株式会社 （英文名称 At Home Co.,Ltd.）",
						'company_name'  => "アットホーム株式会社 （英文名称 At Home Co.,Ltd.）",
						'location'      => "東京都大田区西六郷4-34-12",
						'domain'        => "athome-with.tokyo",
						'applied_start_date' => $now,
						'start_date'         => $now,
						'contract_staff_id'         => "demo_staff_id",
						'contract_staff_name'       => "デモ　太郎",
						'contract_staff_department' => "デモ部署",
						'applied_end_date'          => NULL,
						'end_date'                  => NULL,
						'cancel_staff_id'           => NULL,
						'cancel_staff_name'         => NULL,
						'cancel_staff_department'   => NULL,

						'cp_url'          => "https://cp.athome-hp.jp/Login.aspx",
						'cp_user_id'      => "ah0725976",
						'cp_password'     => "5#iaQUT6",

						'ftp_server_name' => "ftp.athome-hp.jp",
						'ftp_server_port' => 21,
						'ftp_user_id'     => "ah0725976@athome-hp.jp",
						'ftp_password'    => "3ZS5eave",
						'ftp_directory'   => "athome-with.tokyo",
						'ftp_pasv_flg'    => config('constants.ftp_pasv_mode.IN_FORCE'),

						'remarks' => "この情報は初期デモ用となります。"
					));
					
					$id = $row->save();

					//CMSユーザー作成
					$table = App::make(CompanyAccountRepositoryInterface::class);
					$row = $table->create();
					
					$row->company_id = $id;
					$row->login_id = $member_no;
					$row->password = '5#iaQUT6';
					$row->save();
					
					print("Company Account : ". $member_no ."/5#iaQUT6\n");
				}

				DB::commit();

			}catch(\Exception $e) {
				//var_dump($e->getmessage());
				DB::rollback();
				$this->_error->error($e);
				throw $e;
				exit;
			}
		$this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
    
	}
	private function createMemberNo() {

		$table = App::make(CompanyRepositoryInterface::class);
		$member_no = "demo_". mt_rand(111, 999);
		$row = $table->fetchRow(array(["member_no",$member_no]));
		if($row != null) {
			return $this->createMemberNo();
		}
		return $member_no;
	}
}
//C:\xampp>php\php.exe C:\xampp\htdocs\xz-dev-app\application\batch\index.php development appdev CreateCompanyCsv



// docker exec -it servi_80 bash 
// php artisan command:batch-initial-setting development app InitialSetting>> /var/www/html/storage/logs/InitialSetting.log 2>&1

