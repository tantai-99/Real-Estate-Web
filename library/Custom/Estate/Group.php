<?php
namespace Library\Custom\Estate;

use Library\Custom\Kaiin\Kaiin;
use Library\Custom\Kaiin\KaiinList;
use Illuminate\Support\Facades\App;
use App\Repositories\EstateAssociatedCompany\EstateAssociatedCompanyRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;

class Group {
	
	public function __construct() {
	}

	/**
	 * 物件子会社情報を取得する
	 */
	public function getSubCompanies($parentCompanyId) {
		$estateAssociated = App::make(EstateAssociatedCompanyRepositoryInterface::class);
        $apiParam = new Kaiin\KaiinParams();
		$rows = $estateAssociated->getDataByCompanyId($parentCompanyId);
		$companies = array();
        foreach ($rows as $key => $row) {
			$company = new \stdClass();
			$company->memberNo     = $row->subsidiary_member_no;
			$company->createDate   = $row->create_date;
            // 会員APIに接続して会員情報を取得
            $apiParam->setKaiinNo($row->subsidiary_member_no);
            $apiObj = new Kaiin\Kaiin();
            $kaiinDetail = $apiObj->get($apiParam, '会員基本取得');
            if (is_null($kaiinDetail) || empty($kaiinDetail)) {
                $company->memberName = "(取得できませんでした)";
                $company->kaiLinkNo = "（取得できませんでした）";
            }

            if( !array_key_exists('seikiShogo',$kaiinDetail) ||
                !array_key_exists('shogoName',$kaiinDetail['seikiShogo'])){
                $company->memberName = "(取得できませんでした)";
            }else{
                $company->memberName = $kaiinDetail['seikiShogo']['shogoName'];
            }

            if( !array_key_exists('kaiinLinkNo',$kaiinDetail)){
                $company->kaiLinkNo = "（取得できませんでした）";
            }else{
                $company->kaiLinkNo = $kaiinDetail['kaiinLinkNo'];
            }
			$companies[] = $company;
        }
		return $companies;
	}

	/**
	 * 物件子会社情報を取得する（親会員も含める）
	 */
	public function getGroupKaiLinkNoList($parentCompanyId) {

        $kaiinLinkNoList = array();

        // ダミーの会員リンクNoがコンフィグにある場合はそれを使う(debug用)
        $config = getConfigs('v1api.api');

        if (isset($config->dummy_kaiin_link_no)){
            $kaiinLinkNoList = explode(",", $config->dummy_kaiin_link_no);

        // ダミーがない場合は、会員APIから取得する
        }else{

            //グループ主契約(親会社)の会員番号
            $company = App::make(CompanyRepositoryInterface::class)->getDataForId($parentCompanyId);
            $parentMemberNo = $company['member_no'];
            $kaiinNoList = array($parentMemberNo);

            // 物件グループの会員番号を取得
            $eAssTable = App::make(EstateAssociatedCompanyRepositoryInterface::class);
            $childList = $eAssTable->getDataByCompanyId($parentCompanyId);
            $childList = is_null($childList) ? array() : $childList;
            foreach ($childList as $child) {
                array_push($kaiinNoList, $child->subsidiary_member_no);
            }
            // 会員APIから会員番号に対応する会員リンク番号(kaiin_link_no)を取得
            // KApi用パラメータ作成
            $apiParam = new KaiinList\KaiinListParams();
            $apiParam->setKaiinNos($kaiinNoList);
            // 結果JSONを元に要素を作成。
            $apiObj = new KaiinList\KaiinList();
            $kaiinList = $apiObj->get($apiParam, '会員リスト取得');
            $kaiinLinkNoList = array();
            foreach ($kaiinList as $kaiin) {
                array_push($kaiinLinkNoList, $kaiin['kaiinLinkNo']);
            }
        }
		return $kaiinLinkNoList;
	}
}