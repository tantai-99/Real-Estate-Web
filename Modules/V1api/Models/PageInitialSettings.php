<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;

class PageInitialSettings
{
    protected $logger;
    protected $_config;

    private $company;
    private $companyId;
    private $keyword = 'ダミーキーワード';
    private $description = 'ダミー説明';
    private $siteName = 'ダミーサイト名';
    private $companyName = 'ダミー会社名';
    private $memberNo = '';
    /* 主契約の会員リンク番号 */
    private $kaiinLinkNo = '';
    /* 従契約を含む全ての会員リンク番号 */
    private $kaiinLinkNoList = '';
    
    public function __construct($company) {
        // クラス名からモジュール名を取得
        $classNameParts = explode('_', get_class($this));
        $moduleName = strtolower($classNameParts[0]);        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');

        $comRow = $company->getRow();
        $this->company   = $comRow;
        $this->companyId = $comRow->id;
        $this->memberNo = $comRow->member_no;

        // ATHOME_HP_DEV-4841 : 検索利用している種目コード一覧を格納する
        $this->searchSetting = [];
        foreach($company->getSearchSettingRowset() as $searchSetting) {
            foreach(explode(",", $searchSetting['enabled_estate_type']) as $type) {
                $this->searchSetting[] = Estate\TypeList::getShumokuCode(trim($type));
            }
        }

        $hpRow = $company->getHpRow();
        // 初期設定のヘッダー・フッターの会社名
        $this->companyName = $hpRow->company_name;
        $this->keyword = implode(',', array_filter(explode(',',$hpRow->keywords)));
        $this->description = $hpRow->description;
        $this->siteName = $hpRow->title;
        
        /*
         * 会員リンク番号取得処理 
         */
        if (isset($this->_config->dummy_kaiin_link_no))
        {
            $this->logger->debug("<dummy kaiinLinkNoList> ". print_r($this->_config->dummy_kaiin_link_no, true));
            $this->kaiinLinkNoList = $this->_config->dummy_kaiin_link_no;
            // 最初のデータを主契約の会員リンク番号とする。
            $this->kaiinLinkNo = explode(",", $this->kaiinLinkNoList)[0];
        } else {
            // 自身の会員番号
            $kaiinNoList = array($this->memberNo);            
            // 物件グループの会員番号を取得
            $eAssTable = \App::make(EstateAssociatedCompanyRepositoryInterface::class);
            $childList = $eAssTable->getDataByCompanyId($this->companyId);
            $childList = is_null($childList) ? array() : $childList;
            foreach ($childList as $child) {
                array_push($kaiinNoList, $child->subsidiary_member_no);
            }
            // 会員APIから会員番号に対応する会員リンク番号(kaiin_link_no)を取得
            // KApi用パラメータ作成
            $apiParam = new KApi\KaiinListParams();
            $apiParam->setKaiinNos($kaiinNoList);
            // 結果JSONを元に要素を作成。
            $apiObj = new KApi\KaiinList();
            $kaiinList = $apiObj->get($apiParam, '会員リスト取得');
            $this->kaiinLinkNoList = array();
            // 4697 Check Kaiin Stop
            if ($kaiinList) {
                foreach ($kaiinList as $kaiin) {
                    array_push($this->kaiinLinkNoList, $kaiin['kaiinLinkNo']);
                }
                // 最初のデータが主契約の会員リンク番号
                $this->kaiinLinkNo = $this->kaiinLinkNoList[0];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return member_no
     */
    public function getMemberNo()
    {
        return $this->memberNo;
    }
    
    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return mixed
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param mixed $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = (string)filter_var($siteName);
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = (string)filter_var($description);
    }

    /**
     * @return mixed
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * @param mixed $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = (string)filter_var($keyword);
        return $this;
    }

    public function getAllRelativeKaiinLinkNo()
    {
        return $this->kaiinLinkNoList;
    }

    public function getKaiinLinkNo()
    {
        return $this->kaiinLinkNo;
    }
}