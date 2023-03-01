<?php
namespace Modules\V1api\Models;

use App\Repositories\Company\CompanyRepositoryInterface;

class Company
{
    private $params;
    
    /**
     * 加盟店レコードオブジェクト
     * @var App\Models\Company
     */
    private $company;
    
    /**
     * HPレコードオブジェクト
     * @var App\Models\Hp_Row
     */
    private $hp;
    
    /**
     * 物件設定レコードオブジェクト
     * @var App\Models\HpEstateSetting
     */
    private $hpEstateSetting;
    
    private $searchSettings;

    private $specials;
    
    private $special = [];
    
    public function __construct($params)
    {
        
        $this->params = $params;
        // 加盟店情報取得
        $comTable = \App::make(CompanyRepositoryInterface::class);
        $this->company = $comTable->getDataForId($this->params->getComId());
        if (!$this->company || $this->company->isAnalyze() || !$this->company->isAvailable()) {
            throw new \Exception('指定された加盟店は存在しません。');
        }
        
        // 現在のHP取得
        if ($this->params->isAgencyPublish()) {
            $this->hp = $this->company->getCurrentCreatorHp();
        }
        else {
            $this->hp = $this->company->getCurrentHp();
        }
        if (!$this->hp) {
            throw new \Exception('指定されたホームページは存在しません。');
        }
        
        // 各環境毎の物件設定取得
        if ($this->params->isCmsSpecial()) {
            $this->hpEstateSetting = $this->hp->getEstateSetting();
        } else {
            if ($this->isDebugUsersiteUseEstateSetting()) {
                $this->hpEstateSetting = $this->hp->getEstateSetting();
            }
            else if ($this->params->isCmsPublish()) {
                $this->hpEstateSetting = $this->hp->getEstateSetting();
            }
            else if ($this->params->isProdPublish() || $this->params->isAgencyPublish()) {
                $this->hpEstateSetting = $this->hp->getEstateSettingForPublic();
            }
            else if ($this->params->isTestPublish()) {
                $this->hpEstateSetting = $this->hp->getEstateSettingForTest();
            }
        }

        if (!$this->hpEstateSetting) {
            throw new \Exception('指定された物件設定は存在しません。');
        }
    }
    
    /**
     * 
     * @return App\Models\Company
     */
    public function getRow() {
        return $this->company;
    }
    
    /**
     * 
     * @return  App\Models\Hp
     */
    public function getHpRow() {
        return $this->hp;
    }
    
    /**
     * 
     * @return  App\Models\HpEstateSetting
     */
    public function getHpEstateSettingRow() {
        return $this->hpEstateSetting;
    }



    /**
     * @return App\Collections\EstateClassSearchCollection;
     */
    public function getSearchSettingRowset() {
        if (!$this->searchSettings) {
            $this->searchSettings = $this->hpEstateSetting->getSearchSettingAll();
        }
        return $this->searchSettings;
    }

    /**
     * @return App\Collections\EstateClassSearchCollection;
     */
    public function getSearchSettingRowsetPublish() {
        if ($this->hp->getEstateSettingForPublic())
            return $this->hp->getEstateSettingForPublic()->getSearchSettingAll();
        return null;
    }
    
    public function getSpecialRowset() {
        if (!$this->specials) {
            $this->specials = $this->hpEstateSetting->getSpecialAll(['DESC' => 'create_special_date']);
        }
        return $this->specials;
    }
    
    public function getSpecialRowsetRandom() {
            $this->specials = $this->hpEstateSetting->getSpecialAll(['RAND()']);
        
        return $this->specials;
    }
    
    /**
     * 
     * @param string $url
     */
    public function getSpecialRowByUrl($url) {
        if (!isset($this->special[$url])) {
            $this->special[$url] = $this->hpEstateSetting->getSpecialByFilename($url);
        }
        return $this->special[$url];
    }

    /** 編集中の物件設定を常にユーザーサイトに反映させるモードであるかを返却する（デバッグ用）
     *  　　/modules/V1api/configs/debug.iniのusersite_use_estate_setting_flg
     * @param string $url
     */
    private function isDebugUsersiteUseEstateSetting(){

        // デバッグ用なので本番環境ではこのモードを利用できないようにしておく
        if(\App::environment() == "production") {
            return false;
        }
        try {
            $this->config = getConfigs('v1api.debug');

        } catch (\Exception $e) {
            //設定ファイルを読めない場合はデバックモードにしない
            return false;            
        }
        return $this->config->usersite_use_estate_setting_flg;
            
    }
}