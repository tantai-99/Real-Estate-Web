<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\App;
use Library\Custom\Crypt\Password;
use Library\Custom\Crypt\CPPassword;
use Library\Custom\Crypt\FTPPassword;
use App\Models\CompanyAccount;
use App\Models\AssociatedCompanyHp;
use App\Models\Hp;
use App\Models\SecondEstate;
use App\Models\EstateClassSearch;
use App\Models\SpecialEstate;
use App\Models\OriginalSetting;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use Library\Custom\Model\Lists\Original;
use DateTime;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\SecondEstate\SecondEstateRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Traits\MySoftDeletes;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\Tag\TagRepositoryInterface;
use App\Repositories\EstateTag\EstateTagRepositoryInterface;
use App\Repositories\EstateRequestTag\EstateRequestTagRepositoryInterface;
use App\Casts\AsSubString;
use App\Repositories\OriginalSetting\OriginalSettingRepositoryInterface;
class Company extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use MySoftDeletes;

    protected $table = 'company';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'contract_type',
        'cms_plan',
        'reserve_cms_plan',
        'member_no',
        'member_name',
        'company_name',
        'location',
        'domain',
        'initial_start_date',
        'applied_start_date',
        'start_date',
        'contract_staff_id',
        'contract_staff_name',
        'contract_staff_department',
        'reserve_applied_start_date',
        'reserve_start_date',
        'reserve_contract_staff_id',
        'reserve_contract_staff_name',
        'reserve_contract_staff_department',
        'applied_end_date',
        'end_date',
        'cancel_staff_id',
        'cancel_staff_name',
        'cancel_staff_department',
        'map_applied_start_date',
        'map_start_date',
        'map_contract_staff_id',
        'map_contract_staff_name',
        'map_contract_staff_department',
        'cp_url',
        'cp_user_id',
        'cp_password',
        'cp_password_used_flg',
        'ftp_server_name',
        'ftp_server_port',
        'ftp_user_id',
        'ftp_password',
        'ftp_directory',
        'ftp_pasv_flg',
        'remarks',
        'google_map_api_key',
        'map_remarks',
        'full_path',
        'publish_notify',
        'first_publish_date',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
        'top_original_flg'

    ];

    protected $casts = [
        'contract_staff_id' => AsSubString::class.':20',
        'cancel_staff_id' => AsSubString::class.':20',
        'reserve_contract_staff_id' => AsSubString::class.':20',
        'map_contract_staff_id' => AsSubString::class.':20',
    ];

    public function init()
    {
        $this->_cryptMap['password'] = new Password();
        $this->_cryptMap['cp_password'] = new CPPassword();
        $this->_cryptMap['ftp_password'] = new FTPPassword();
    }

    /**
     * Get the companyAccount associated with the Company
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function companyAccount()
    {
        return $this->hasOne(CompanyAccount::class, 'company_id');
    }

    public function associatedCompanyHp()
    {
        return $this->hasOne(AssociatedCompanyHp::class, 'company_id');
    }

    public function secondEstate()
    {
        return $this->hasOne(SecondEstate::class, 'company_id');
    }

    public function originalSetting() {
        return $this->hasOne(OriginalSetting::class, 'company_id');
    }

    public function tag()
    {
        return $this->hasOne(Tag::class, 'company_id');
    }

    public function estateTag()
    {
        return $this->hasOne(EstateTag::class, 'company_id');
    }

    public function estateRequestTag()
    {
        return $this->hasOne(EstateRequestTag::class, 'company_id');
    }

	public function associatedCompany() {
        return $this->hasOne(AssociatedCompany::class, 'subsidiary_company_id');
    }

    public function logEdit()
    {
        return $this->hasMany(LogEdit::class, 'company_id');
    }

    public function publishProgress()
    {
        return $this->hasMany(PublishProgress::class, 'company_id');
    }

    /**
     * 利用可能期間内か確認する
     * @return boolean
     */
    public function isAvailable()
    {
        $now = time();

        // 診断のみの場合期間チェックなし
        if ($this->isAnalyze()) {
            return true;
        }

        if ($this->start_date == null) {
            return false;
        }

        if (strtotime($this->start_date) > $now) {
            return false;
        }

        if ($this->end_date && strtotime(date('Y-m-d', strtotime($this->end_date))) + 86400 < $now) {
            return false;
        }

        return true;
    }

    /**
     * 地図オプションが利用可能期間内か確認する
     * @return boolean
     */
    public function isMapOptionAvailable()
    {
        $now = time();

        if ($this->cms_plan == config('constants.cms_plan.CMS_PLAN_ADVANCE')) {
            return true;
        }

        if (!$this->map_start_date || (strtotime($this->map_start_date) > $now)) {
            return false;
        }

        if (($this->map_end_date != null) && (strtotime(date('Y-m-d', strtotime($this->map_end_date))) + 86400 < $now)) {
            return false;
        }

        return true;
    }

    /**
     * 診断閲覧のみか確認する
     * @return boolean
     */
    public function isAnalyze()
    {
        $isAnalyze = config('constants.company_agreement_type.CONTRACT_TYPE_ANALYZE') == $this->contract_type;
        if ($isAnalyze) {
            if ($this->end_date && strtotime(date('Y-m-d', strtotime($this->end_date))) + 86400 >= time()) {
                $isAnalyze = false;
            }
        }
        return $isAnalyze;
    }


    /**
     * デモ会員かどうかを取得する
     * @return boolean
     */
    public function isDemo($flag = true)
    {
        $isDemo = config('constants.company_agreement_type.CONTRACT_TYPE_DEMO') == $this->contract_type;
        if ($isDemo && $flag) {
            if ($this->end_date && strtotime(date('Y-m-d', strtotime($this->end_date))) + 86400 >= time()) {
                $isDemo = false;
            }
        }
        return $isDemo;
    }

    /**
     * HPを取得する
     * @return App\Models\Hp
     */
    public function getCurrentHp()
    {
        return $this->_getHpBy('current_hp_id');
    }

    public function getCurrentCreatorHp()
    {
        return $this->_getHpBy('space_hp_id');
    }

    public function getBackupHp()
    {
        return $this->_getHpBy('backup_hp_id');
    }

    protected function _getHpBy($col)
    {
        $assocRow = $this->associatedCompanyHp()->first();
        if (!$assocRow || !$assocRow->{$col}) {
            return false;
        }
        return $assocRow->hp($col)->first();
    }

    /**
     * HPを作成する
     */
    public function createHp()
    {
        $assocTable = App::make(AssociatedCompanyHpRepositoryInterface::class);
        $assocRow = $this->associatedCompanyHp()->first();
        // $assocTable->fetchRow(array(['company_id', $this->id]));
        if (!$assocRow) {
            $assocRow = $assocTable->create();
            $assocRow->company_id = $this->id;
        }

        $hpTable = App::make(HpRepositoryInterface::class);
        $hpRow = $hpTable->create(array(
            'initial_setting_status_code' => config('constants.hp.INITIAL_SETTING_STATUS_NEW'),
            'side_layout' => '',
            'global_navigation' => config('constants.original.DEFAULT_GLOBAL_NAVIGATION')
        ));
        $hpRow->save();

        $assocRow->current_hp_id = $hpRow->id;
        $assocRow->save();

        return $hpRow;
    }

    public function getDisplayCompanyName()
    {
        return isset($this->member_name) ? $this->member_name : $this->company_name;
    }

    public function getSiteUrl()
    {
        return 'https://www.' . $this->domain;
    }

    /**
     * サイトのドメイン名（本契約の場合、営業デモ用ドメイン）を返す
     * ∵本契約の場合、利用ドメインが変更されてしまうから
     * ※本番用サイトURLが必要なときは「getSiteUrl()」を使用のこと
     */
    public function getSiteDomain()
    {
        $domain = $this->domain;
        if ($this->contract_type == config('constants.company_agreement_type.CONTRACT_TYPE_PRIME')) {
            // 本契約なので、テストサイト用ドメインは、設定ファイルで決め打ちのにする ATHOME_HP_DEV-2197
            $config = getConfigs('sales_demo');
            $domain = "{$this->member_no}.{$config->demo->domain}";
        }
        return $domain;
    }

    public function fetchTag()
    {

        $no_columns = array();
        $no_columns["id"] = "id";
        $no_columns["delete_flg"] = "delete_flg";
        $no_columns["create_id"] = "create_id";
        $no_columns["create_date"] = "create_date";
        $no_columns["update_id"] = "update_id";
        $no_columns["update_date"] = "update_date";

        $new_tags = new \stdClass();
        // MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
        $tag = $this->tag()->first();
        if ($tag) {
            foreach ($tag->toArray() as $key => $val) {
                if (isset($no_columns[$key])) continue;
                $new_tags->$key = $val;
            }
        } else {
            $cols = \App::make(TagRepositoryInterface::class)->getTableColumns();
            foreach ($cols as $key => $value) {
                if (isset($no_columns[$value])) continue;
                $new_tags->$value = "";
            }
        }
        // MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
        $estate_tag = $this->estateTag()->first();
        if ($estate_tag) {
            foreach ($estate_tag->toArray() as $key => $val) {
                if (isset($no_columns[$key])) continue;
                $new_tags->$key = $val;
            }
        } else {
            $cols = \App::make(EstateTagRepositoryInterface::class)->getTableColumns();
            foreach ($cols as $key => $value) {
                if (isset($no_columns[$value])) continue;
                $new_tags->$value = "";
            }
        }
        // MySQLの8K問題に対応（経緯詳細はATHOME_HP_DEV-5067)
        $estate_request_tag = $this->estateRequestTag()->first();
        if ($estate_request_tag) {
            foreach ($estate_request_tag->toArray() as $key => $val) {
                if (isset($no_columns[$key])) continue;
                $new_tags->$key = $val;
            }
        } else {
            $cols = \App::make(EstateRequestTagRepositoryInterface::class)->getTableColumns();
            foreach ($cols as $key => $value) {
                if (isset($no_columns[$value])) continue;
                $new_tags->$value = "";
            }
        }
        return $new_tags;
    }

    public function __get($columnName)
    {
        switch ($columnName) {
            case "initial_start_date_view":
            case "reserve_applied_start_date_view":
            case "reserve_start_date_view":
            case "applied_start_date_view":
            case "applied_end_date_view":
            case "start_date_view":
            case "end_date_view":
            case "map_applied_start_date_view":
            case "map_start_date_view":
            case "map_applied_end_date_view":
            case "map_end_date_view":
                $columnNameWithoutView = substr($columnName, 0, -5);;
                $this->attributes[$columnName]    = str_replace("-", "/", substr($this->attributes[$columnNameWithoutView], 0, 10));
                if ($this->attributes[$columnName] == "0000/00/00") {
                    $this->attributes[$columnName] = "";
                }
                break;
        }
        return parent::__get($columnName);
    }

    /**
     * 自分に紐付いている子companyを取得する
     * @return App\Collections\CustomCollection
     */
    public function fetchAssociatedCompanies()
    {
        /** @var $table App\Repositories\Company\CompanyRepository */
        $table = App::make(CompanyRepositoryInterface::class);
        return $table->fetchAssociatedCompanies($this->id);
    }

    public function getSecondEstate()
    {
        $table = App::make(SecondEstateRepositoryInterface::class);
        return $table->getDataForCompanyId($this->id);
    }

    public function fetchCompnayAccountRow()
    {

        return $this->companyAccount()->first();
    }

    /**
     * 公開済みの物件検索設定を削除
     */
    public function deletePublicSearch()
    {
        $hp = $this->getCurrentHp();
        $setting = $hp->getEstateSettingForPublic();
        if (!$setting) {
            return;
        }

        $search = $setting->getSearchSettingAllWithPubStatus();
        if (!$search) {
            return;
        }
        $where = [['hp_estate_setting_id', $setting->id], ['hp_id', $hp->id]];
        $delete = App::make(EstateClassSearchRepositoryInterface::class);
        $delete->delete($where, true);
    }
    public function deletePublicSpecial()
    {
        $hp = $this->getCurrentHp();
        $setting = $hp->getEstateSettingForPublic();
        if (!$setting) {
            return;
        }
        $special = $setting->getSpecialAllWithPubStatus();
        if (!$special) {
            return;
        }
        $where = [['hp_estate_setting_id', $setting->id], ['hp_id', $hp->id]];
        $delete = App::make(SpecialEstateRepositoryInterface::class);
        $delete->delete($where, true);
    }

    /**
     * Check top original flag
     * @param bool $ignorePlan
     * @return bool
     */
    public function checkTopOriginal($ignorePlan = false)
    {
        $checkPlan = !$ignorePlan;

        if ($checkPlan) {
            if (!Original::checkPlanCanUseTopOriginal($this->cms_plan)) {
                return false;
            }
        }

        $originalSetting = $this->originalSetting()->first();

        if (null == $originalSetting || !$originalSetting->start_date) {
            return false;
        }

        $start  = new DateTime($originalSetting->start_date);
        $today  = new DateTime(date("Y-m-d"));

        $ret = false;
        // ATHOME_HP_DEV-4448: Change condition check Top original
        if ($originalSetting->all_update_top == 0) {
            if ($start instanceof DateTime && $start->getTimestamp() <= $today->getTimestamp()) {
                $ret = true;
                $finish = $originalSetting->end_date ? new DateTime($originalSetting->end_date) : null;
                if ($finish instanceof DateTime && $finish->getTimestamp() <= $today->getTimestamp()) {
                    $ret = false;
                }
            }
        } else {
            if ($start instanceof DateTime && $start->getTimestamp() <= $today->getTimestamp()) {
                $ret = true;
                $finish = $originalSetting->end_date ? new DateTime($originalSetting->end_date) : null;
                if ($finish instanceof DateTime && $finish->getTimestamp() < $today->getTimestamp()) {
                    $ret = false;
                }
            }
        }
        // end 4448

        return $ret;
    }


    /**
     * @param bool|App\Models\Hp $hp
     * @param bool $topTo
     * @param bool $topBefore
     * @throws Exception
     */
    public function topOriginalEvent($hp, $topTo = false, $topBefore = false)
    {
        if (!$hp) return;
        App::make(HpPageRepositoryInterface::class)->generateTopOriginalData($hp, $topTo, $topBefore);
    }

    /**
     * @param boolean $master
     * @return null|BaseRepository|App\Models\OriginalSetting
     */
    public function getOriginalSetting($master = false)
    {
        if ($master) {
            return App::make(OriginalSettingRepositoryInterface::class)->getDataForCompanyId($this->id);
        }
        return App::make(OriginalSettingRepositoryInterface::class)->getDataForCompanyId($this->id);
    }


    /**
     * HPを取得する
     * @param $message
     * @return App\Models\Hp
     * @throws Exception
     */
    public function getCurrentHpOrFail($message = '')
    {
        if (!$row = $this->getCurrentHp()) {
            throw new \Exception($message);
        }
        return $row;
    }
}
