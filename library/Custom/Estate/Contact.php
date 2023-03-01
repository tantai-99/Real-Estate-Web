<?php
namespace Library\Custom\Estate;

use Modules\V1Api\Models\BApi;
use Modules\V1Api\Models\KApi;

class Contact {

	public function getEstateData($company, $apiData, $estateId) {

		// サイト主契約の会員
		$siteKaiinNo = $company['member_no'];

        // サイト主契約会員グループの会員リンクNoのリスト
        $estateGroup = new Group();
        $groupKaiLinkNoList = $estateGroup->getGroupKaiLinkNoList($company['id']);

        // BApi用パラメータを作成して、物件APIから該当の物件情報を取得
        $bukkenApiObj    = new BApi\BukkenId();
        $bukkenApiParams = new BApi\BukkenIdParams();
        $bukkenApiParams->setGroupId($company['id']);
        $bukkenApiParams->setKaiinLinkNo($groupKaiLinkNoList);
        $bukkenApiParams->setId($estateId);
        $bukken = $bukkenApiObj->search($bukkenApiParams, 'DETAIL');

        $dispModel = $bukken['display_model'];
        $dataModel = $bukken['data_model'];
	
        $kaiinApiObj = new KApi\Kaiin();
		$kaiinApiParam = new KApi\KaiinParams();        

        // 2次広告自動公開の物件は、主契約の会員情報を表示
        $isNijiKoukokuJidou = $this->isNijiKoukokuJidou($company,$dispModel);
        if ($isNijiKoukokuJidou) {
            // サイト会員を会員情報とする
            $kaiinApiParam->setKaiinNo($siteKaiinNo);
            $kaiinInfo = (object) $kaiinApiObj->get($kaiinApiParam, '会員基本取得');

            // 2次広告自動公開物件の元づけ会員の情報も出力する
            $kaiinApiParam->setKaiinNo($dispModel['csite_muke_kaiin_no']);
            $secondEstateKaiinInfo = (object) $kaiinApiObj->get($kaiinApiParam, '会員基本取得');

        // それ以外の物件は、物件情報の会員情報を表示
        } else {
            $kaiinApiParam->setKaiinNo($dispModel['csite_muke_kaiin_no']);
            $kaiinInfo = (object) $kaiinApiObj->get($kaiinApiParam, '会員基本取得');
        }        

		// 物件情報と会員情報を返す
		$estateData = array(
			'estate-id'	         => $estateId,
			'bukken'             => $bukken,
			'kaiinInfo'          => $kaiinInfo,
            'domain'             => $company->domain,
            'shumoku'            => $apiData['bukken_type'],
			);

        // 2次広告自動公開情報
        $estateData['secondEstate'] = ( $isNijiKoukokuJidou ) ? $secondEstateKaiinInfo : null;
        $estateData['second_estate_flg']=$isNijiKoukokuJidou;
		return $estateData;
	}

    /**
     * 対象の物件が２次広告自動公開物件かどうかを取得する
     *   判定方法
     * 　　　物件の会員リンクNoが物件グループの会員リンクNoに含まれていれば、通常物件
     * 　　　物件の会員リンクNoが物件グループの会員リンクNoに含まれていなければ、２次広告自動公開物件
     * 
    */
    private function isNijiKoukokuJidou($siteCompany, $dispModel) {
        return $dispModel['niji_kokoku_jido_kokai_fl'];
        //error_log(print_r(($dispModel['niji_kokoku_jido_kokai_fl']?"true":"false"),1));
        //error_log(print_r($dispModel['kaiin_link_no'],1));
/*
        // アドバンス側で判定するロジック
        $estateGroup = new Group();
        $bukkenKaiLinkNo = $dispModel['kaiin_link_no'];
        $groupKaiLinkNoList = $estateGroup->getGroupKaiLinkNoList($siteCompany['id']);
        return !(in_array($bukkenKaiLinkNo, $groupKaiLinkNoList));
*/
    }
}