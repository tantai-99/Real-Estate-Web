<?php
namespace App\Console\Commands\batch\controllers;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use App\Console\Commands\batch\BatchAbstract;
use App\Repositories\AssociatedCompanyFdp\AssociatedCompanyFdpRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Library\Custom\Model\Estate\FdpType;
use Library\Custom\Kaiin\RemarcKeiyakuList\RemarcKeiyakuList;
use Library\Custom\Kaiin\RemarcKeiyakuList\RemarcKeiyakuListParams;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;

class FDPEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'command:batch-fdp-event {env?} {app?} {controller?}';

   /**
    * The console command description.
    *
    * @var string
    */
   protected $description = 'Command fdp event';

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

            error_reporting(E_ALL ^ E_WARNING);

            $associatedCompanyFdpTablle = App::make(AssociatedCompanyFdpRepositoryInterface::class);
            $companyTable = App::make(CompanyRepositoryInterface::class);

            $select=$companyTable->model()->withoutGlobalScopes()->select();
            $where = array(
                ['delete_flg', 0],
                ['cms_plan', '>' ,config('constants.cms_plan.CMS_PLAN_LITE')],
                'whereNotIn' => ['contract_type',[config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')]]
                //'cms_plan NOT IN (?)' => config('constants.cms-plan.CMS_PLAN_LITE'),
                //'contract_type NOT IN (?)' => config('constants.company_agreement_type.CONTRACT_TYPE_DEMO')
            );

            $listCompanys = $companyTable->fetchAll($where)->toArray();
            
            $companySlices = [];
            $maxValue = FdpType::FDP_MAX_VALUE;

            if ($listCompanys && !empty($listCompanys)) {
                for ($i=0; $i < count($listCompanys)/$maxValue; $i++) {
                    $companySlices[] = array_slice($listCompanys, $i*$maxValue, $maxValue);
                }

                try {
                    DB::beginTransaction();
                    for ($i=0; $i < count($companySlices); $i++) {
                        $this->_info->info('Update Information FDP: Running...index '.$i.' start');
                        $memberNos = [];
                        foreach ($companySlices[$i] as $companySlice) {
                            $memberNos[] = $companySlice["member_no"];
                        }
                        try {
                            $memberFDP = $this->getRemarcKeiyakuKaiinNos($memberNos);
                            $this->genaralFDP($memberFDP, $associatedCompanyFdpTablle);
                        } catch (\Exception $e) {
                            $this->_error->error('Update Information FDP: Failed... index '.$i.' = '.$e->getMessage(), false, true);
                        }
                        $this->_info->info('Update Information FDP: Running...index '.$i.' end');
                    }
                    $this->_info->info('Up Information FDP: Success');
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->_error->error('Update Information FDP: Failed = '.$e->getMessage());
                }
            }
        $this->_info->info('done all.');
        $this->_info->info('//////////////// END ////////////////');
        }catch (\Exception $e) {
            $this->_error->error($e);
        }
    }

    private function getRemarcKeiyakuKaiinNos(array $targetKaiinNos) {
        $riyoDateKaiinNos = array();
        $kaiinList = $this->getRemarcKeiyakuList($targetKaiinNos);
        $kainnLists = [];
        if ($kaiinList) {
            foreach ($kaiinList as $kaiin) {
                 $kainnLists[$kaiin['kaiinNo']] = $kaiin;
            }
        }

        foreach ($targetKaiinNos as $targetKaiinNo) {
            if (!empty($this->isRiyoDateKaiin($targetKaiinNo, $kainnLists))) {
                $riyoDateKaiinNos[] = $this->isRiyoDateKaiin($targetKaiinNo, $kainnLists);
            }
        }

        return $riyoDateKaiinNos;
    }

    private function getRemarcKeiyakuList(array $targetKaiinNos) {
        $apiParam = new RemarcKeiyakuListParams();
        $apiParam->setKaiinNos($targetKaiinNos);

        $apiObj = new RemarcKeiyakuList();
        $kaiinList = $apiObj->get($apiParam, '会員リスト取得', false);
        if (!empty($kaiinList)) {
            if (isset($kaiinList['model'])) {
                $kaiinList = $kaiinList['model'];
            } else {
                $kaiinList = [];
            }
        }
        return $kaiinList;

    }

    private function isRiyoDateKaiin($targetKaiinNo, array $apiKaiinList) {
        $isDate = [];
        if (isset($apiKaiinList[$targetKaiinNo]) && isset($apiKaiinList[$targetKaiinNo]["areaPlanRiyoStartDate"])) {
            // 3717 Change riyoStartDate -> areaPlanRiyoStartDate, riyoStopDate-> areaPlanRiyoStopDate
            // 4751: Change condintion check FDP
            $areaPlanRiyoStopDate = isset($apiKaiinList[$targetKaiinNo]["areaPlanRiyoStopDate"]) ? $apiKaiinList[$targetKaiinNo]["areaPlanRiyoStopDate"] : null;
            $isFDP = FdpType::checkDateUseFDP($apiKaiinList[$targetKaiinNo]["areaPlanRiyoStartDate"], $areaPlanRiyoStopDate);
            $companyTable = $companyTable = App::make(CompanyRepositoryInterface::class);
            $associatedCompanyFdpTablle = App::make(AssociatedCompanyFdpRepositoryInterface::class);
            $company = $companyTable->getDataForMemberNo($targetKaiinNo);
            $company = empty($company->toArray()) ? '' : $company->toArray()[0];
            // 4274 Change spec form FDP contact
            // 4489: Remove setting search housing CMS
            $hp = '';
            $hpSpace = '';
            $hpBackup = '';
            $id = '';
            $cmpPlan = '';
            $member_no = '';
            if ($company) {
                // 4274 Change spec form FDP contact
                // 4489: Remove setting search housing CMS
                $hp = $companyTable->getDataForId($company["id"])->getCurrentHp();
                $hpSpace = $companyTable->getDataForId($company["id"])->getCurrentCreatorHp();
                $hpBackup = $companyTable->getDataForId($company["id"])->getBackupHp();
                $id = $company["id"];
                $cmpPlan = $company["cms_plan"];
                $member_no = $company["member_no"];
            }
            $fdpRow = $associatedCompanyFdpTablle->fetchRowByCompanyId($id);
            $fdpRow = ($fdpRow) ? true : false;
            // 3717 Change riyoStartDate -> areaPlanRiyoStartDate, riyoStopDate-> areaPlanRiyoStopDate
            $riyoStartDate = $apiKaiinList[$targetKaiinNo]["areaPlanRiyoStartDate"];
            $riyoStopDate = $areaPlanRiyoStopDate;
            // 4489: Remove setting search housing CMS
            $isDate = array("id" => $id, "cms_plan" => $cmpPlan, "member_no" => $member_no, 'hp' => $hp, 'space_hp' => $hpSpace, 'backup_hp' => $hpBackup) + array("is_fdp" => $isFDP, "fdp_start_date" => $riyoStartDate, "fdp_end_date" => $riyoStopDate, "associate_fdp" => $fdpRow);
        }
        return $isDate;
    }

    /* 4274 Change spec form FDP contact
    private function insertContactFDP ($hp) {
        if (!$hp) return;
        $table = App::make(HpPageRepositoryInterface::class);
        $hpPage = $table->fetchAll(array('delete_flg = 0', 'page_type_code' => HpPageRepository::TYPE_FORM_FDP_CONTACT, 'hp_id' => $hp->id))->toArray();
        if (!$hpPage) {
            $type = HpPageRepository::TYPE_FORM_FDP_CONTACT;
            $id = $table->insert(array(
                'new_flg' => 1,
                'page_type_code' => $type,
                'page_category_code' => $table->getCategoryByType($type),
                'title' => $table->getTypeNameJp($type),
                'description' => $table->getDescriptionNameJp($type),
                'keywords' => $table->getKeywordNameJp($type),
                'filename' => $table->getPageNameJp($type),
                'level' => 1,
                'sort' => 4,
                'hp_id' => $hp->id,
            ));
            $row = $table->fetchRow(array('id = ?' => $id));
            $row['link_id'] = $id;
            $row->save();
        }
    }

    private function deleteContactFDP ($hp) {
        if (!$hp) return;
        $table = App::make(HpPageRepositoryInterface::class);
        $hpPage = $table->fetchAll(array('delete_flg = 0', 'page_type_code = ?' => HpPageRepository::TYPE_FORM_FDP_CONTACT, 'hp_id = ?' => $hp->id))->toArray();
        if ($hpPage) {
            $table->update(array('delete_flg' => 1), array('hp_id = ?' => $hp->id, 'page_type_code = ?' => HpPageRepository::TYPE_FORM_FDP_CONTACT));
        }
    }
    */
    private function genaralFDP($members, &$associatedCompanyFdpTablle) {
        foreach ($members as $member) {
            // change condition display FDP
            if ($member["associate_fdp"]) {
                // 4564: Change default setting housing FDP
                // $beforeFDP = FdpType::checkbeforeFDP($member['id']);
                // if (!$beforeFDP && $member["is_fdp"]) {
                //     FdpType::updateDefaultFDP($member["hp"]);
                //     // 4688 Set default FDP Agency
                //     FdpType::updateDefaultFDP($member["space_hp"]);
                //     FdpType::updateDefaultFDP($member["backup_hp"]);
                // }
                $associatedCompanyFdpTablle->update(array(['company_id', $member['id']]), array('start_date' => $member["fdp_start_date"], 'end_date' => $member["fdp_end_date"]));
                // 4733: Check condition update default setting housing
                if ($member["is_fdp"]) {
                    FdpType::updateDefaultFDP($member["hp"], false);
                    FdpType::updateDefaultFDP($member["space_hp"], false);
                    FdpType::updateDefaultFDP($member["backup_hp"], false);
                } else {
                    // 4274 Change spec form FDP contact
                    // // delete contact hp
                    // $this->deleteContactFDP($member["hp"]);
                    // // delete contact space/creator hp
                    // $this->deleteContactFDP($member["space_hp"]);
                    // // delete contact backup hp
                    // $this->deleteContactFDP($member["backup_hp"]);
                    // 4489: Remove setting search housing CMS
                    // Update setting search FDP hp
                    FdpType::updateSettingSearchFDP($member["hp"]);
                    // Update setting search FDP space/creator hp
                    FdpType::updateSettingSearchFDP($member["space_hp"]);
                    // Update setting search FDP backup hp
                    FdpType::updateSettingSearchFDP($member["backup_hp"]);
                }
            } else if (!empty($member["fdp_start_date"])) {
                $associatedCompanyFdpTablle->save($member['id'], $member["fdp_start_date"], $member["fdp_end_date"]);
                // 4751: Change condintion check FDP
                if ($member["is_fdp"]) {
                    FdpType::updateDefaultFDP($member["hp"], true);
                    FdpType::updateDefaultFDP($member["space_hp"], true);
                    FdpType::updateDefaultFDP($member["backup_hp"], true);
                } else {
                    FdpType::updateSettingSearchFDP($member["hp"]);
                    FdpType::updateSettingSearchFDP($member["space_hp"]);
                    FdpType::updateSettingSearchFDP($member["backup_hp"]);
                }
            }
                // 4274 Change spec form FDP contact
                // // inset contact hp
                // $this->insertContactFDP($member["hp"]);
                // // inset contact space/creator hp
                // $this->insertContactFDP($member["space_hp"]);
                // // inset contact backup hp
                // $this->insertContactFDP($member["backup_hp"]);
        }
    }
}


// docker exec -it servi_80 bash 
// php artisan command:batch-fdp-event development app FDPEvent>> /var/www/html/storage/logs/FDPEvent.log 2>&1
