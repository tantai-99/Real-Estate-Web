<?php
namespace Modules\V1api\Services\Pc;

use Modules\V1api\Services;
use Modules\V1api\Models\Params;
use Modules\V1api\Models\Settings;
use Modules\V1api\Models\Datas;
use Library\Custom\Model\Estate;

class Detail extends Services\AbstractElementService
{
	public $head;
	public $header;
    public $content;
    public $isFDP;
    public $breadCrumb;
	
	public function create(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$this->head = $this->head($params, $settings, $datas);
		$this->header = $this->header($params, $settings, $datas);
        $this->content = $this->content($params, $settings, $datas);
        $this->isFDP = $this->isFDP($params, $settings);
	}
	
	public function check(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            if (is_null($datas->getBukken())) {
                throw new \Exception('', 404);
            }
        }
        if(is_null($settings->search->getSearchSettingRowByTypeCt($params->getTypeCt()))) {
            throw new \Exception('', 404);
        }
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if ($params->getTab() == 3 && (!Services\ServiceUtils::isFDP($settings->page) 
            || !Services\ServiceUtils::canDisplayFdp(Estate\FdpType::TOWN_TYPE, $fdp))) {
            throw new \Exception('', 404);
        }
	}
	
	private function head(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pageInitialSettings = $settings->page;
		$siteName = $pageInitialSettings->getSiteName();
		$keyword = $pageInitialSettings->getKeyword();
		$comName = $pageInitialSettings->getCompanyName();
		$description = $pageInitialSettings->getDescription();
	
		$pNames = $datas->getParamNames();

        // 4697 Check Kaiin Stop
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            // 物件情報の取得
            $bukken = $datas->getBukken();
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            // 種目情報の取得
            $shumoku    = $dispModel->csite_bukken_shumoku_cd[0];
            // $shumoku_nm = Services\ServiceUtils::getShumokuNameByCd($shumoku);
            $shumoku_nm = $pNames->getShumokuName();
            
            $bukken_id = $dispModel->id;
            $bukken_name = Services\ServiceUtils::getVal('csite_bukken_title', $dispModel);
            $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);
            $bukken_info = $bukken_name . ' ' . $dispModel->shikugun_nm . 'の' . $dispModel->shumoku_nm;

            // {物件検索の情報と同じ}の物件情報｜{$物件種目}｜{$CMS初期設定サイト名}：{$物件ID}       
            $title_txt   = "${bukken_info}の物件情報｜${shumoku_nm}｜${siteName}：${bukken_id}";
            $keyword_txt = "${bukken_name},${shumoku_nm},${keyword}";
            // 【{$会社名}】{物件検索の情報と同じ}の（{$物件種目}）物件詳細。{$CMS初期設定サイトの説明}
            $desc_txt   = "【${comName}】${bukken_info}の(${shumoku_nm})物件詳細。${description}";
        } else {
            $shumoku_nm = $pNames->getShumokuName();
            $title_txt   = "物件情報｜${shumoku_nm}｜${siteName}";
            $keyword_txt = "${shumoku_nm},${keyword}";
            // 【{$会社名}】{物件検索の情報と同じ}の（{$物件種目}）物件詳細。{$CMS初期設定サイトの説明}
            $desc_txt   = "【${comName}】(${shumoku_nm})物件詳細。${description}";
        }
        
        if ($params->getTab() == 2)
        {
            $title_txt = '周辺環境｜'.$title_txt;
            $keyword_txt = '周辺環境,'.$keyword_txt;
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $desc_txt   = "【${comName}】${bukken_info}の(${shumoku_nm})の周辺環境。${description}";
            } else {
                $desc_txt = "【${comName}】(${shumoku_nm})の周辺環境。${description}";
            }
        }
        if ($params->getTab() == 3)
        {
            $title_txt = '街のこと（統計情報）｜'.$title_txt;
            $keyword_txt = '街のこと（統計情報）,'.$keyword_txt;
            // 4697 Check Kaiin Stop
            if (Services\ServiceUtils::checkKaiin($settings->page)) {
                $desc_txt   = "【${comName}】${bukken_info}の(${shumoku_nm})の街のこと（統計情報）。${description}";
            } else {
                $desc_txt = "【${comName}】(${shumoku_nm})の街のこと（統計情報）。${description}";
            }
        }
		$head = new Services\Head();
		$head->title = $title_txt;
		$head->keywords = $keyword_txt;
		$head->description = $desc_txt;
	
		return $head->html();
	}

	private function header(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
		$pNames = $datas->getParamNames();
        // 4697 Check Kaiin Stop
        $shumoku_nm = $pNames->getShumokuName();
        if (Services\ServiceUtils::checkKaiin($settings->page)) {
            // 物件情報の取得
            $bukken = $datas->getBukken();
            $dataModel = (object) $bukken['data_model'];
            $dispModel = (object) $bukken['display_model'];

            // 種目情報の取得
            // $shumoku    = $dispModel->csite_bukken_shumoku_cd[0];
            // $shumoku_nm = Services\ServiceUtils::getShumokuNameByCd($shumoku);
            
            $bukken_id = $dispModel->id;
            $bukken_name = Services\ServiceUtils::getVal('csite_bukken_title', $dispModel);
            $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);
        }

        // ({$物件種目}){物件検索の情報と同じ}の物件情報
        // ({$物件種目}){物件検索の情報と同じ}の周辺環境
        switch ($params->getTab()) {
            case 1:
                $suffix = '物件情報';
                break;
            case 2:
                $suffix = '周辺環境';
                break;
            case 3:
                $suffix = '街のこと（統計情報）';
                break;
        }
        
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            return "<h1 class='tx-explain'>(${shumoku_nm})${suffix}</h1>";
        }
        return "<h1 class='tx-explain'>(${shumoku_nm})${bukken_name}の${suffix}</h1>";
	}
	
	private function content(
			Params $params,
			Settings $settings,
			Datas $datas)
	{
        // 4697 Check Kaiin Stop
        if (!Services\ServiceUtils::checkKaiin($settings->page)) {
            $doc = $this->getTemplateDoc("/".Services\ServiceUtils::checkDateMaitain().".tpl");
            return $doc->html();
        }
		$doc = $this->getTemplateDoc("/detail/content.tpl");
	
		// 変数
		$comName = $settings->page->getCompanyName();
		$searchCond = $settings->search;
	
		$pNames = $datas->getParamNames();

        // 会社名
        $comName = $settings->page->getCompanyName();

        $bukken = $datas->getBukken();

        $dataModel = (object) $bukken['data_model'];
        $dispModel = (object) $bukken['display_model'];
        
        // 種目情報の取得
        $type_ct = $params->getTypeCt();
        $shumoku    = $pNames->getShumokuCd();
        $shumoku_nm = $pNames->getShumokuName();
        // 市区郡の取得
        $shikugun_cd = $this->getVal('shozaichi_cd1', $dataModel, true);
        // 都道府県の取得
        $ken_cd  = substr($shikugun_cd, 0, 2);
        $ken_ct = Services\ServiceUtils::getKenCtByCd($ken_cd);
        $ken_nm  = $dispModel->ken_nm;
        // 物件APIから取得
        $shikugun_ct = Services\ServiceUtils::getShikugunObjByCd($ken_cd, $shikugun_cd)->shikugun_roman;
        $shikugun_nm = $this->getVal('shikugun_nm', $dispModel);

        // 物件名
        $bukken_name = $this->getVal('csite_bukken_title', $dispModel);
        $bukken_name = Services\ServiceUtils::replaceSsiteBukkenTitle($bukken_name);

        /*
         * パンくず作成
         * 市区町村選択検索パターンで作成する。
         */
        $top = $this->getSearchTopFileName($searchCond);        
        $levels = [
            // URL = NAME
            "/${top}"      => $this::BREAD_CRUMB_1ST_SHUMOKU
        ];
        if (count($datas->getPrefSetting()) !== 1) {
            $levels += [
                "/${type_ct}/" => $this::BREAD_CRUMB_2ND_PREF
            ];
        }
        $levels += [
            "/${type_ct}/${ken_ct}/" => "${ken_nm}",
            "/${type_ct}/${ken_ct}/result/${shikugun_ct}-city.html" => "${shikugun_nm}の物件一覧"
        ];
        if ($params->getTab() == 1)
        {
            $levels += array('' => "${bukken_name}");
        } elseif ($params->getTab() == 2) {
            $tmp = sprintf('/%s/detail-%s',$type_ct, $params->getBukkenId());
            $levels += array($tmp => "${bukken_name}");
            $levels += array('' => "周辺環境");
        } else {
            $tmp = sprintf('/%s/detail-%s',$type_ct, $params->getBukkenId());
            $levels += array($tmp => "${bukken_name}");
            $levels += array('' => "街のこと（統計情報）");
        }
        $this->breadCrumb = $this->createBreadCrum($doc['div.breadcrumb'], $levels);

        $detailMaker = new Element\Detail();
        $detailElem = $detailMaker->createElement($shumoku, $settings->page, $bukken, $params, $datas->getCodeList(), $searchCond);
        $doc['div.contents-left.contents-article']->replaceWith($detailElem);

        /*
         * 最近見た物件
         */
        $historyElem = $doc['div.contents-right section.side-watch'];
        $bukkenList = $datas->getHistoryKoma();
        $total = $bukkenList['total_count'];
        if (is_null($params->getHistory()) || $total == 0) {
            $historyElem->remove();
        } else {
            $komaMaker = new Element\Koma();
			// ATHOME_HP_DEV-4841 : 第4引数として、PageInitialSettings を追加
			$komaMaker->createHistoryKoma($historyElem, $bukkenList, $params, $settings->page);
        }
        
        return $doc->html();
    }

    private function isFDP(
            Params $params,
            Settings $settings)
    {
        $settingRow = $settings->search->getSearchSettingRowByTypeCt($params->getTypeCt())->toSettingObject();
        $fdp = json_decode($settingRow->display_fdp);
        if (Services\ServiceUtils::isFDP($settings->page) && count($fdp->fdp_type)) {
            return true;
        }
        return false;
    }
}