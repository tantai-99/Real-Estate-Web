<?php 
namespace Library\Custom\Model\Estate;
use App\Repositories\AssociatedCompanyFdp\AssociatedCompanyFdpRepositoryInterface;
use App\Repositories\HpPage\HpPageRepository;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use Library\Custom\Kaiin\RemarcKeiyaku;
use App;
use DateTime;

class FdpType extends AbstractList {

    const FACILITY_INFORMATION_TYPE = 1;
    const ELEVATION_TYPE = 2;
    const TOWN_TYPE = 3;

    const TOWN_RESIDENT_TYPE = 1;
    const TOWN_HOUSEHOLD_TYPE = 2;
    const TOWN_BEDTOWN_TYPE = 3;
    const TOWN_GENDER_TYPE = 4;
    const TOWN_OWNERSHIP_TYPE = 5;
    const TOWN_RESIDENCE_TYPE = 6;

    const TOWN_EDIT_LABEL = 1;
    const TOWN_DETAIL_LABLE = 2;

    const FDP_NOT_REGISTER_LABLE = "※不動産データプロ・エリア情報プランをご契約の方のみご利用いただけます。";
    const FDP_LINK_DISABLE_LABLE = "「不動産データプロ」の詳細はこちら";
    const FDP_LINK_DISABLE_URL = "https://business.athome.jp/service/datapro/";
    const FDP_TITLE = "周辺エリア情報設定";

    // #4259 Change value 1000->500
    const FDP_MAX_VALUE = 500;

    public static $fdp_titles = [
        self::FACILITY_INFORMATION_TYPE    => "周辺施設情報",
        self::ELEVATION_TYPE               => "道のりと高低差",
        self::TOWN_TYPE                    => "街のこと",
    ];

    public static $town_titles = [
        self::TOWN_RESIDENT_TYPE        => "近隣の住民",
        self::TOWN_HOUSEHOLD_TYPE       => "近隣の世帯数",
        self::TOWN_GENDER_TYPE          => "近隣の男女比",
        self::TOWN_BEDTOWN_TYPE         => "近隣のベッドタウン傾向",
        self::TOWN_OWNERSHIP_TYPE       => "近隣の持ち家比率",
        self::TOWN_RESIDENCE_TYPE       => "近隣の居住期間",
    ];

    public static $town_labels = [
        self::TOWN_EDIT_LABEL       => "「街のこと」詳細設定",
        self::TOWN_DETAIL_LABLE     => "「街のこと」設定項目",
    ];

    public static $town_class = [
        self::TOWN_RESIDENT_TYPE        => "population-chart",
        self::TOWN_HOUSEHOLD_TYPE       => "households-chart",
        self::TOWN_GENDER_TYPE          => "gender-chart",
        self::TOWN_BEDTOWN_TYPE         => "bedtown-chart",
        self::TOWN_OWNERSHIP_TYPE       => "ownership-chart",
        self::TOWN_RESIDENCE_TYPE       => "residence-chart",
    ];

    public function __construct()
    {
        $this->associatedCompanyFdpRepository = App::make(AssociatedCompanyFdpRepositoryInterface::class);
        $this->companyRepository = App::make(CompanyRepositoryInterface::class); 
    }

    public static function getFdp() {
        return self::$fdp_titles;
    }

    public function getFdpTitle() {
        return self::FDP_TITLE;
    }

    public static function getTown() {
        return self::$town_titles;
    }

    public function getTownLabel() {
        return self::$town_labels;
    }

    public static function getTownClass() {
        return self::$town_class;
    }

    public function getFdpNotUse() {
        return [
            self::FDP_NOT_REGISTER_LABLE,
            self::FDP_LINK_DISABLE_LABLE,
            self::FDP_LINK_DISABLE_URL,
        ];
    }

    public function isFDP($company) {
        if(!self::checkPlanCanUseFDP($company->cms_plan)){
            return false;
        }

        $companyFdp = $this->associatedCompanyFdpRepository->fetchRowByCompanyId($company->id);
        if (!$companyFdp) {
            return false;
        }

        $startDate = $companyFdp["start_date"];
        $endDate = $companyFdp["end_date"];
        return self::checkDateUseFDP($startDate, $endDate);
    }

    public function getRiyoDate($memberNo) {
        $apiParam = new RemarcKeiyaku\RemarcKeiyakuParams();
        $apiParam->setKaiinNo($memberNo);
        $apiObj = new RemarcKeiyaku\GetRemarcKeiyaku();
        $riyoData = $apiObj->get($apiParam, '会員基本取得');
        if (is_null($riyoData) || empty($riyoData)) {
            return array('riyoStart' => '','riyoStop' => '');
        }
        $riyoData = (object)$riyoData;
        // #3717 Change riyoStartDate -> areaPlanRiyoStartDate, riyoStopDate-> areaPlanRiyoStopDate
        return array('riyoStart' => $riyoData->areaPlanRiyoStartDate,'riyoStop' => $riyoData->areaPlanRiyoStopDate);
    }

    public function getLabelFdp($fdp) {
        $fdpList = array();
        if(count($fdp) > 0) {
            foreach ($fdp as $value) {
                array_push($fdpList, self::$fdp_titles[$value]);
            }
        }
        return implode('　', $fdpList);
    }

    public function getLabelTown($town) {
        $townList = array();
        if(count($town) > 0) {
            foreach ($town as $value) {
                if ($value != -1) {
                    array_push($townList, self::$town_titles[$value]);
                }
            }
        }
        return implode('　', $townList);
    }

    /**
     * Check Plan Can Use FDP
     * @param string $plan
     * @return bool
     */
    public function checkPlanCanUseFDP($plan){
        if (empty($plan) || $plan == config('constants.cms_plan.CMS_PLAN_LITE')) {
            return false;
        }
        return true;
    }

    public static function checkDateUseFDP($startDate, $endDate){
        if (!$startDate || $startDate == '0000-00-00 00:00:00') {
            return false;
        }

        $start  = new DateTime($startDate);
        $today  = new DateTime(date("Y-m-d"));

        $ret = false;
        if ($start instanceof DateTime && $start->getTimestamp() <= $today->getTimestamp()) {
            $ret = true;

            $endDate = ($endDate == '0000-00-00 00:00:00') ? null : $endDate;
            $finish = $endDate ? new DateTime($endDate) : null;
            // change condition display FDP
            if ($finish instanceof DateTime && $finish->getTimestamp() < $today->getTimestamp()) {
                $ret = false;
            }
        }
        
        return $ret;
    }

    public function setDateFormFDP($companyId) {
        $companyFdp = $this->associatedCompanyFdpRepository->fetchRowByCompanyId($companyId);
        $riyo = array('riyoStart' => '', 'riyoStop' => '');
        if ($companyFdp) {
            // change condition display FDP
            if (isset($companyFdp['start_date']) && !(substr($companyFdp['start_date'], 0, 10) == "0000-00-00")) {
                $riyo["riyoStart"] = date_format(date_create($companyFdp["start_date"]), "Y/m/d");
            }
            // change condition display FDP
            if (isset($companyFdp["end_date"]) && !(substr($companyFdp['end_date'], 0, 10) == "0000-00-00")) {
                $riyo["riyoStop"] = date_format(date_create($companyFdp["end_date"]), "Y/m/d");
            }
        }
        return $riyo;
    }

    public function getPeripheralType($pageTypeCode, $company) {
        $isFDP = self::isFDP($company);
        return $isFDP && in_array($pageTypeCode, [HpPageRepository::TYPE_FORM_LIVINGLEASE, HpPageRepository::TYPE_FORM_OFFICELEASE, HpPageRepository::TYPE_FORM_LIVINGBUY, HpPageRepository::TYPE_FORM_OFFICEBUY]);
    }

    // #4417 Check FDF frontend
    public function isFrontFDP($company) {
        $companyFdp = $this->associatedCompanyFdpRepository->fetchRowByCompanyId($company->id);
        if (!$companyFdp) {
            return false;
        }

        $startDate = $companyFdp["start_date"];
        $endDate = $companyFdp["end_date"];
        return self::checkDateUseFDP($startDate, $endDate);
    }

    public function updateFdpByMemberNo($memberNo, $cmsPlan, $companyId) {
        if (self::checkPlanCanUseFDP($cmsPlan)) {
            $date = self::getRiyoDate($memberNo);
            $companyFdp = $this->associatedCompanyFdpRepository->fetchRowByCompanyId($companyId);
            // change condition display FDP
            if ($companyFdp) {
                // 4489: Remove setting search housing CMS
                $this->associatedCompanyFdpRepository->update(array(['company_id' => $companyId]), array('start_date' => $date["riyoStart"], 'end_date' => $date["riyoStop"]));
                if (!self::checkDateUseFDP($date["riyoStart"], $date["riyoStop"])) {
                    $hp = $this->companyRepository->getDataForId($companyId)->getCurrentHp();
                    self::updateSettingSearchFDP($hp);
                    $hpSpace = $this->companyRepository->getDataForId($companyId)->getCurrentCreatorHp();
                    self::updateSettingSearchFDP($hpSpace);
                    $hpBackup = $this->companyRepository->getDataForId($companyId)->getBackupHp();
                    self::updateSettingSearchFDP($hpBackup);
                }
            } else {
                $this->associatedCompanyFdpRepository->save($companyId, $date["riyoStart"],  $date["riyoStop"]);
            }
        }
    }

    // 4489: Change UI setting FDP
    public function getDefaultSettingFDP() {
        return array('fdp_type' => ["1","3"], 'town_type' => ["1","2"]);
    }

    public function getDefaultSettingNotFDP() {
        return array('fdp_type' => [], 'town_type' => []);
    }
    // end 4489

    // 4489: Remove setting search housing CMS
    public static function updateSettingSearchFDP($hp) {
        if (!$hp) return;
        $settingSearch = App::make(HpEstateSettingRepositoryInterface::class)->getSetting($hp->id,config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $estate = App::make(EstateClassSearchRepositoryInterface::class);
        if (isset($settingSearch->id)) {
            // 4733: Check condition update default setting housing
            $estateClass = $estate->getSettingAll($hp->id, $settingSearch->id)->toArray();
            if (!empty($estateClass) && !($estateClass[0]['display_fdp'] == '{"fdp_type":[],"town_type":["-1"]}')) {
                $estate->update(array( ['hp_id', $hp->id], ['hp_estate_setting_id', $settingSearch->id]), array('display_fdp' => '{"fdp_type":[],"town_type":["-1"]}'));
            }
        }
    }

    // 4564: Change default setting housing FDP
    // public static function checkbeforeFDP($companyId) {
    //     $companyFdpTable = App::make(AssociatedCompanyFdpRepositoryInterface::class);
    //     $companyFdp = $companyFdpTable->fetchRowByCompanyId($companyId);
    //     if (!$companyFdp) {
    //         return false;
    //     }
    //     $startDate = $companyFdp["start_date"];
    //     $endDate = $companyFdp["end_date"];
    //     return self::checkDateUseFDP($startDate, $endDate);
    // }

    public static function updateDefaultFDP($hp, $flg) {
        if (!$hp) return;
        $settingSearch = App::make(HpEstateSettingRepositoryInterface::class)->getSetting($hp->id,config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $estate = App::make(EstateClassSearchRepositoryInterface::class);
        if (isset($settingSearch->id)) {
            // 4733: Check condition update default setting housing
            $estateClass = $estate->getSettingAll($hp->id, $settingSearch->id)->toArray();
            if ((!empty($estateClass) && $estateClass[0]['display_fdp'] == '{"fdp_type":[],"town_type":["-1"]}') || $flg) {
                $estate->update(array( ['hp_id', $hp->id], ['hp_estate_setting_id', $settingSearch->id]), array('display_fdp' => '{"fdp_type":["1","3"],"town_type":["1","2"]}'));
            }
        }
    }
    // end 4564

    // 4622: Change css button popup contact FDP
    public function listColorContact() {
        return [
            'elegant' => ['#66553d', '#66553d', '#5e4f39'],
            'japanese' => ['#000000', '#4B4845', '#101010'],
            'feminine' => ['#E37630', '#f5ba1d', '#f5ba1d'],
            'simple' => ['#000000', '#000000', '#000000'],
            'katame01' => ['#3A3E4A', '#5D6477', '#384057'],
            'luxury01' => ['#807448', '#e5cb82', '#B7A667'],
            'retro01' => ['#22292F', '#6888a1', '#495968'],
            'default' => ['#E37630', '#FF8341', '#FF5C00'],
        ];
    }
}