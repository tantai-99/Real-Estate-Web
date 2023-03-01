<?php
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;
use DateTime;
use DateTimeZone;
use Library\Custom\Logger\Publish as LoggerPublish;
use Library\Custom\Publish\Prepare\Page;
use App\Repositories\PublishProgress\PublishProgressRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use Library\Custom\Publish\Ftp;
use Library\Custom\Ftp as CustomFtp;
use Library\Custom\Publish\Render\Content;
use App\Http\Form\Publish as FormPublish;
use Library\Custom\Publish\Special;
use Library\Custom\Publish\Estate\Make;
use Illuminate\Support\Facades\DB;
use App\Models\HpEstateSetting;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;

class PublishReserve extends Command {

    public  $reserveAll;
    public  $reserveSpecialAll;
    public  $reserveInHp;
    public  $reserveSpecialInHp;
    public  $currentHpId;
    private $dt;
    private $logger;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'command:batch-publish-reserve {env?} {app?} {controller?}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command publish reserve';
 
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
            set_time_limit(0);

            // 予約を取得
            $this->reserveAll        = App::make(ReleaseScheduleRepositoryInterface::class)->fetchReseveRowsBeforeNow();
            $this->reserveSpecialAll = App::make(ReleaseScheduleSpecialRepositoryInterface::class)->fetchReseveRowsBeforeNow();

            // 予約なければ終了
            if ($this->reserveAll->count() < 1 && $this->reserveSpecialAll->count() < 1) {
                $this->_info->info('//////////////// END ////////////////');
                return;
            }

            // 現在時刻取得
            $this->dt = new DateTime();
            $this->dt->setTimeZone(new DateTimeZone('Asia/Tokyo'));

            $this->logger = LoggerPublish::getInstance();

            // NHP-2801 公開処理中の処理中可否判断の対策:定数取得
            $publishConfig = getConfigs('publish')->publish;

            // HPごとに処理
            foreach ($this->getHpIds() as $hpId) {

                $this->currentHpId = $hpId;

                // NHP-5101 対象HP-IDの予約リストの初期化
                $this->reserveInHp = null;
                $this->reserveSpecialInHp= null;

                $page = new Page($this->currentHpId, ['action' => 'reserve']);



                // 解約済みの場合はcontinue
                if (!$this->isValidAccount($page)) {
                    continue;
                }

                $params = $this->getDummyParams($page->getPages());

                $page->getNamespace('publish')->params = $params;

                if (!($publishType = $this->publishType())) {
                    continue;
                };

                // 進捗ログレコード作成(NHP-4617)
                $progressTable   = App::make(PublishProgressRepositoryInterface::class);
                $progressId = $progressTable->createProgress([
                    'publish_type' => $publishType,
                    'company_id' => App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hpId)->id,
                    'hp_id' => $hpId,
                    'login_id' => App::make(PublishProgressRepositoryInterface::class)::$batchLoginId,    // login_idはないので『batch』固定
                    'success_notify' => 0,    // 成功通知不要
                    'all_upload_flg' => 0     // <- 予約では全公開不可=0
                ]);

                $render = new Content($this->currentHpId, $publishType, $page);
                $ftp    = new Ftp($this->currentHpId, $publishType);

                $this->logger->init($page->getHpRow(), $page->getCompanyRow());

                // pages
                $newPages = $page->getAfterPages($publishType, $params);
                $render->setPages($newPages);

                // spcial
                $currentAt   = FormPublish::NOW;
                $reserveList = (new Special\Prepare\Testsite($page->getHpRow()))->reserveList($params);
                $special     = Special\Make\Rowset::getInstance();
                $special->init($page->getHpRow(), $params, $currentAt, $reserveList, true);

                // search
                $estate = Make\Publish::getInstance();
                $estate->init($page->getHpRow());

                // NHP-2801 公開処理中の処理中可否判断の対策:排他チェック(画面上からと違い例外検知不要)
                // 代行更新でも company.full_path への書込みを実施するため、hp.idではなくcompnay.id でロックする
                $company = App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($hpId);
                $getLockKey = sprintf("%s_%d", $publishConfig->lock_key_prefix, $company->id);
                $row = DB::select(sprintf("SELECT IS_FREE_LOCK('%s') AS LOCK_RES", $getLockKey));
                
                if(!$row[0]->LOCK_RES) {	// 別公開処理稼働中
                    $this->_info->info(sprintf("// hp_id=%d skipped : %s //", $hpId, $publishConfig->exclusive_error_msg));
                    $progressTable->publishFinish($progressId, 0, $publishConfig->exclusive_error_msg);
                    continue;
                }
                // NHP-2801 公開処理中の処理中可否判断の対策:ロック実行
                $row = DB::select(sprintf("SELECT GET_LOCK('%s', %d) AS LOCK_RES", $getLockKey, $publishConfig->lock_wait));
                if(!$row[0]->LOCK_RES) {	// ロックに失敗
                    $this->_info->info(sprintf("// hp_id=%d skipped : %s //", $hpId, $publishConfig->exclusive_error_msg));
                    $progressTable->publishFinish($progressId, 0, $publishConfig->exclusive_error_msg);
                    continue;
                }

                try {
                    DB::beginTransaction();
                    
                    App::make(HpRepositoryInterface::class)->fetchRow(array(['id',1]));

                    $render->putHtmlFiles();

                    $ftp->login();

                    if ($publishType == config('constants.publish_type.TYPE_PUBLIC')) {
                        $this->initPublicDir($ftp->getCompany());
                    }

                    $ftp->fullPath($publishType);

                    $ftp->close();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, 'fullpath.php完了');

                    $render->init();

                    // 非公開領域
                    $render->view();
                    $render->script();
                    $render->setting();

                    // 公開領域
                    if ($publishType != config('constants.publish_type.TYPE_PUBLIC') || $page->getHpRow()->all_upload_flg) {

                        $render->js();
                        $render->css();
                        $render->imgs();
                    }

                    $render->directPublic();
                    $newImageIds = $render->images();
                    $newFile2Ids = $render->file2s();
                    $newFileIds  = $render->files();
                    $render->qrcode();
                    $render->logo();
                    $render->favicon();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, 'レンダリング完了');

                    // 進捗ログページ数カウント(NHP-4617)
                    $progressTable->countPages($progressId, $hpId, $publishType);

                    $zips = $render->getZip();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, 'Zip生成完了');

                    $ftp->login();

                    foreach ($zips as $zip) {
                    list($uploadres, $remoteFile) = $ftp->uploadZip($zip);

                    if(!$uploadres) {
                        // 再度FTP接続のやり直し
                        $ftp->close(true);	// 強制切断
                        $ftp->login();

                        exec("ls -l $zip | awk '{print $5}'", $res);
                        $localSize = $res[0];
                        $remoteSize = $ftp->getSize($remoteFile);

                        if($localSize != $remoteSize) {
                            $msg = 'アップロードに失敗しました。';
                            throw new \Exception($msg);
                            }
                        }
                    }

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, 'Zipアップロード完了');

                    $ftp->commit($publishType);
                    $ftp->close();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, 'commit.php実行完了');

                    $render->updateHtmlFiles();
                    $render->zipHtml();

                    // DB更新
                    $updatePageIds = $this->getUpdatePageIds();
                    $release       = isset($updatePageIds['release']) ? $updatePageIds['release'] : [];
                    $close         = isset($updatePageIds['close']) ? $updatePageIds['close'] : [];

                    $page->updatePage($newPages, $release, $close);
                    $page->updateHp( $newImageIds, $newFile2Ids, $newFileIds )	;
                    if ($estate->estateSetting instanceof HpEstateSetting) {

                        // 物件検索のお問い合わせを公開中に
                        App::make(HpPageRepositoryInterface::class)->updateStatuseEstateContactPageAll($hpId);

                        $ids = [];
                        if (isset($params['special'])) {
                            foreach ($params['special'] as $id => $value) {

                                if (!$value['update']) {
                                    continue;
                                }

                                // simple
                                if (!isset($value['new_release_flg'])) {
                                    $ids[] = $id;
                                    continue;
                                }

                                // detail release
                                if ($value['new_release_flg'] && !$value['new_release_at']) {
                                    $ids[] = $id;
                                    continue;
                                }

                                // close release
                                if ($value['new_close_flg'] && !$value['new_close_at']) {
                                    $ids[] = $id;
                                    continue;
                                }
                            }
                        }
                        App::make(SpecialEstateRepositoryInterface::class)->updatePublishedAt($ids);
                        $estate->estateSetting->copyToPublic($special->filterPublicIds());
                    }

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, '公開ステータス更新完了');

                    $this->completeReserve();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, '予約反映済み設定完了');

                    $render->afterPublish();
                    //$page->getNamespace()->unsetAll();

                    // 進捗ログ更新(NHP-4617)
                    $progressTable->updateProgress($progressId, '後処理完了');

                    DB::commit();

                    // 進捗ログ更新：正常終了=1 (NHP-4617)
                    $progressTable->publishFinish($progressId, 1);

                    // NHP-2801 公開処理中の処理中可否判断の対策:ロック解放
                    $stmt = DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));
                    
                } catch (\Exception $e) {

                    // ロールバック前に公開ログを確保する(NHP-4617)
                    $progRow = $progressTable->fetchRow([[ 'id',$progressId ]]);

                    DB::rollback();

                    // 進捗ログ更新：異常終了=0 (NHP-4617)
                    // $progressTable->publishFinish($progressId, 0, $e->getMessage());
                    // エラー設定ののち、保存する
                    $date = new DateTime();
                    $progRow->status = 0;
                    $progRow->progress.= sprintf("[%s] %s\n", $date->format('Y-m-d H:i:s'), '異常終了');
                    $progRow->finish_time = $date->format('Y-m-d H:i:s');
                    $progRow->exception_msg = $e->getMessage();
                    $progRow->save();

                    // NHP-2801 公開処理中の処理中可否判断の対策:ロック解放
                    $stmt = DB::select(sprintf("SELECT RELEASE_LOCK('%s') AS LOCK_RES", $getLockKey));
                    
                    $ftp->close();
                    // throw の中から、errorを起動するため不要
                    // $this->error($e);
                    throw $e;
                }
            }
            $this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    /**
     * 処理対象のホームページIDを取得
     *
     * @return array
     */
    private function getHpIds() {

        $res = [];
        foreach ($this->reserveAll as $row) {
            if (!$this->isValidHpId($row->hp_id)) {
                continue;
            }
            $res[] = $row->hp_id;
        }
        foreach ($this->reserveSpecialAll as $row) {
            if (!$this->isValidHpId($row->hp_id)) {
                continue;
            }
            $res[] = $row->hp_id;
        }
        return array_unique($res);
    }

    private function isValidHpId($hpId) {

        $table = App::make(AssociatedCompanyHpRepositoryInterface::class);

        // current hp id をチェック
        $assoc = $table->fetchRowByCurrentHpId($hpId);
        if ($assoc) {
            return true;
        }

        // space hp id をチェック
        $assoc = $table->fetchRowBySpaceHpId($hpId);
        if ($assoc) {
            return true;
        }

        // それでもなければfalse
        return false;
    }

    private function publishType() {

        $company = App::make(CompanyRepositoryInterface::class)->fetchRowByHpId($this->currentHpId);
        if (!$company) {
            return false;
        }
        $assoc = App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRow(array(['company_id',$company->id]));
        if (!$assoc) {
            return false;
        }
        if ($assoc->current_hp_id == $this->currentHpId) {
            return config('constants.publish_type.TYPE_PUBLIC');
        }
        if ($assoc->space_hp_id == $this->currentHpId) {
            return config('constants.publish_type.TYPE_SUBSTITUTE');
        }

        return false;
    }

    /**
     * ダミーのパラメータ作成
     *
     * @param $pages
     * @return array
     */
    private function createDummyParams($pages) {

        $dummy = [];
        foreach ($pages as $page) {

            $dummy['page'][$page['id']]['update']          = 0;
            $dummy['page'][$page['id']]['new_release_flg'] = 0;
            $dummy['page'][$page['id']]['new_release_at']  = 0;
            $dummy['page'][$page['id']]['new_close_flg']   = 0;
            $dummy['page'][$page['id']]['new_close_at']    = 0;
        }
        return $dummy;
    }

    /**
     * ダミーのパラメータに予約の変更を判定
     *
     * @param $reserveList
     * @param $dummy
     * @return mixed
     */
    private function getDummyParams($pages) {

        // ダミーデータ
        $dummy = $this->createDummyParams($pages);

        // 日時ごとに予約を取得
        $reserveList = [];
        foreach ($this->reserveAll as $row) {
            if ($row->hp_id != $this->currentHpId) {
                continue;
            }
            $reserveList[$row->release_at][] = $row;
        }

        // 時系列に変更を反映
        foreach ($reserveList as $array) {
            foreach ($array as $row) {
                if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                    $dummy['page'][$row->page_id]['update']          = 1;
                    $dummy['page'][$row->page_id]['new_release_flg'] = 1;
                    $dummy['page'][$row->page_id]['new_release_at']  = 0;
                }
                elseif ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                    $dummy['page'][$row->page_id]['update']        = 1;
                    $dummy['page'][$row->page_id]['new_close_flg'] = 1;
                    $dummy['page'][$row->page_id]['new_close_at']  = 0;
                }
            }
        }

        $reserveList = [];
        foreach ($this->reserveSpecialAll as $row) {
            if ($row->hp_id != $this->currentHpId) {
                continue;
            }
            $reserveList[$row->release_at][] = $row;
        }
        foreach ($reserveList as $array) {
            foreach ($array as $row) {
                if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                    $dummy['special'][$row->special_estate_id]['update']          = 1;
                    $dummy['special'][$row->special_estate_id]['new_release_flg'] = 1;
                    $dummy['special'][$row->special_estate_id]['new_release_at']  = 0;
                    $dummy['special'][$row->special_estate_id]['new_close_flg']   = 0;
                    $dummy['special'][$row->special_estate_id]['new_close_at']    = 0;
                }
                elseif ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                    $dummy['special'][$row->special_estate_id]['update']          = 1;
                    $dummy['special'][$row->special_estate_id]['new_release_flg'] = 0;
                    $dummy['special'][$row->special_estate_id]['new_release_at']  = 0;
                    $dummy['special'][$row->special_estate_id]['new_close_flg']   = 1;
                    $dummy['special'][$row->special_estate_id]['new_close_at']    = 0;
                }
            }
        }
        return $dummy;
    }

    /**
     * HPの予約を取得
     *
     * @return array
     */
    private function getReseveCurrentHp() {

        if ($this->reserveInHp) {
            return $this->reserveInHp;
        }

        $res = [];
        foreach ($this->reserveAll as $row) {
            if ($row->hp_id != $this->currentHpId) {
                continue;
            }
            $res[] = $row;
        }
        return $this->reserveInHp = $res;
    }

    private function getReseveSpecialCurrentHp() {

        if ($this->reserveSpecialInHp) {
            return $this->reserveSpecialInHp;
        }

        $res = [];
        foreach ($this->reserveSpecialAll as $row) {
            if ($row->hp_id != $this->currentHpId) {
                continue;
            }
            $res[] = $row;
        }
        return $this->reserveSpecialInHp = $res;
    }

    /**
     * 今回更新されたページIDを取得
     *
     * @return array
     */
    private function getUpdatePageIds() {

        $res = [];
        foreach ($this->getReseveCurrentHp() as $row) {

            if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                $res['release'][] = $row->page_id;
            }
            elseif ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                $res['close'][] = $row->page_id;
            }
        }
        return $res;
    }

    /**
     * 予約を完了に更新
     */
    private function completeReserve() {
        // page
        foreach ($this->getReseveCurrentHp() as $row) {
            $row->completion_flg = 1;
            $row->save();
        }

        // special
        foreach ($this->getReseveSpecialCurrentHp() as $row) {
            $row->completion_flg = 1;
            $row->save();
        }
    }

    /**
     *
     *
     * @return bool
     */
    private function isValidAccount($page) {

        // 解約日時未設定
        if (is_null($page->getCompanyRow()->end_date)) {
            return true;
        };

        if ($page->getCompanyRow()->end_date <= $this->dt->format('Y-m-d H:i:s')) {
            return false;
        };

        return true;
    }

    /**
     * publicディレクトリの初期化
     *
     * @param $companyRow
     */
    private function initPublicDir($companyRow) {

        $table = App::make(HpPageRepositoryInterface::class);

        if ($this->notPublishYet($table->getTopPageData($this->currentHpId))) {

            $cftp = new CustomFtp($companyRow->ftp_server_name);

            $cftp->login($companyRow->ftp_user_id, $companyRow->ftp_password);
            if ($companyRow->ftp_pasv_flg == config('constants.ftp_pasv_mode.IN_FORCE')) {
                $cftp->pasv(true);
            }

            $cftp->deleteFolderBelow($companyRow->ftp_directory);
            $cftp->close();
        }
    }

    /**
     * 過去にパブリッシュされたことがないか判定
     *
     * @param $toppageRow
     *
     * @return bool
     */
    private function notPublishYet($toppageRow) {

        return $toppageRow->public_flg === 0 && $toppageRow->republish_flg === 0;
    }
}


// docker exec -it servi_80 bash 
// php artisan command:batch-publish-reserve development app PublishReserve>> /var/www/html/storage/logs/PublishReserve.log 2>&1
 