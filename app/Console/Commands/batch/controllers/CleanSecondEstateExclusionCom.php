<?php

/** 2次広告自動公開除外会員に設定された会員のうち、退会した会員を削除する
 *
 *　退会判定は基幹システムの退会に依存する（会員APIにて判定する）
 *
 * テーブル
 * 　　
 *
 */
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepositoryInterface;
use Library\Custom\Kaiin\KaiinList\KaiinListParams;
use Library\Custom\Kaiin\KaiinList\KaiinList;
use Illuminate\Support\Facades\DB;

class CleanSecondEstateExclusionCom extends Command
{

    // 30件ずつ
    const PER_PAGE = 30;

    /**
     * @var App\Repositories\SecondEstateExclusion\SecondEstateExclusionRepositoryInterface:
     */
    private $secondExclusionTbl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:batch-clean-second-estate-exclusion-com {env?} {app?} {controller?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command clean second estate exclusion com';

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

            $totalRiyoTeishiKaiinNo = [];
            for($page=1; ;$page++){

                // 除外設定会員を取得
                $excludedKaiinNos = $this->getExcludedKaiinNos( $page );
                if( empty($excludedKaiinNos) ){
                    break;
                }

                //除外設定された会員のうち、契約終了になっている会員のリストを取得する
                $riyoTeishiKaiinNos = $this->getRiyoTeishiKaiinNos($excludedKaiinNos);
                $totalRiyoTeishiKaiinNo = array_merge($totalRiyoTeishiKaiinNo, $riyoTeishiKaiinNos);

                if( count($excludedKaiinNos) < self::PER_PAGE) {
                    break;
                }
            }

            // 利用停止になった会員の除外設定を削除する
            if( !empty($totalRiyoTeishiKaiinNo) ){
                $this->cleanExcludedKaiin($totalRiyoTeishiKaiinNo);
            }
            $this->_info->info('//////////////// END ////////////////');
        } catch (\Exception $e) {
            $this->_error->error($e);
        }
    }


    /**
     * 除外設定されている会員を取得する
     *
     * @return array
     */
    private function getExcludedKaiinNos( $page ){

        $count  = self::PER_PAGE;
        $offset = ($page-1)*self::PER_PAGE;

        $seExclusionTbl = App::make(SecondEstateExclusionRepositoryInterface::class);

        // 検索条件
        $rowSet = $seExclusionTbl->getExcluded($count, $offset);

        $excludedKaiinNos = array();
        foreach ($rowSet as $row) {
            $excludedKaiinNos[] = $row->member_no;
            $this->_info->info('excluded setting( kaiin='.$row->member_no.' )' );

        }
        return $excludedKaiinNos;
    }

    /**
     * 利用停止会員を取得する
     * @param array $targetKaiinNos
     * @return array 利用停止会員Noの配列
     */
    private function getRiyoteishiKaiinNos(array $targetKaiinNos)
    {
        $riyoteishiKaiinNos = array();

        // 会員APIから会員情報を取得する

        $kaiinList = $this->getKaiinList($targetKaiinNos);
        foreach ($targetKaiinNos as $targetKaiinNo) {

            // 利用停止中の会員は除外設定から削除する
            if ( !$this->isAliveKaiin($targetKaiinNo, $kaiinList) ) {
                $riyoteishiKaiinNos[] = $targetKaiinNo;
            }
        }
        return $riyoteishiKaiinNos;
    }

    private function getKaiinList(array $targetKaiinNos)
    {
        // KApi用パラメータ作成
        $apiParam = new KaiinListParams();
        $apiParam->setKaiinNos($targetKaiinNos);

        // 結果JSONを元に要素を作成。
        $apiObj = new KaiinList();
        $kaiinList = $apiObj->get($apiParam, '会員リスト取得');
        return $kaiinList;

    }

    /** 会員が利用中かどうかを取得する
     * @param $targetKaiinNo
     * @param array $kaiinList
     */
    private function isAliveKaiin($targetKaiinNo, array $apiKaiinList)
    {
        $isAlive = false;

        foreach( $apiKaiinList as $kaiin ){
            $kaiin = (object)$kaiin;

            // 会員情報を取得できて、利用停止フラグがOnでなければ利用中と判定
            if( $kaiin->kaiinNo == $targetKaiinNo ) {
                if( property_exists($kaiin,'isRiyoStop') && $kaiin->isRiyoStop ) {
                    $isAlive = false;
                    break;
                }
                $isAlive = true;
                break;
            }
        }
        return $isAlive;
    }


    /**
     * 利用停止会員の２次広告除外設
     * @param array $riyoTeishiKaiinNos
     *
     */
    private function cleanExcludedKaiin(array $riyoTeishiKaiinNos)
    {

        if(empty($riyoTeishiKaiinNos)){
            return;
        }

        $seExclusionTbl = App::make(SecondEstateExclusionRepositoryInterface::class);
        DB::beginTransaction();

        try {

            foreach ($riyoTeishiKaiinNos as $kaiinNo) {
                $this->_info->info('excluded setting( kaiin='.$kaiinNo.' ) is removed because kaiin is leaved.');

                $data = array();
                $data['delete_flg'] = 1;
                $data['delete_by_riyostop_flg'] = 1;
                $where = array(["member_no", $kaiinNo], ["delete_flg", 0]);
                $seExclusionTbl->update($where, $data);

            }
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
// php artisan command:batch-clean-second-estate-exclusion-com development app CleanSecondEstateExclusionCom >> /var/www/html/storage/logs/CleanSecondEstateExclusionCom.log 2>&1